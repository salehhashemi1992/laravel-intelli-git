<?php

namespace Salehhashemi\LaravelIntelliGit\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use InvalidArgumentException;
use Salehhashemi\LaravelIntelliGit\OpenAi;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AiCommitCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $name = 'ai:commit';

    /**
     * The console command description.
     */
    protected $description = 'Create a new commit using AI';

    public function __construct(private readonly OpenAi $openAi)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check for staged changes
        $stagedChanges = $this->hasStagedChanges();

        if (! $stagedChanges) {
            $this->info('No staged changes found.');
            $unstagedChanges = $this->hasUnstagedChanges();

            if ($unstagedChanges && $this->confirm('There are unstaged changes. Would you like to stage all changes?')) {
                $this->stageAllChanges();
            } else {
                $this->info('No unstaged changes found or staging cancelled.');

                return 0;
            }
        }

        $diff = $this->getLimitedDiff();
        $prompt = $this->createAiPrompt($diff);

        try {
            $commitDetails = $this->fetchAiGeneratedContent($prompt);
            $this->commit($commitDetails);
        } catch (InvalidArgumentException $e) {
            $this->error('An error occurred when generating the commit message: '.$e->getMessage());

            return 1;
        } catch (RequestException $e) {
            $this->error('An error occurred when communicating with the OpenAI service: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Check for staged changes.
     */
    private function hasStagedChanges(): bool
    {
        $process = Process::fromShellCommandline('git diff --staged --quiet');
        $process->run();

        // Process will be successful when there's no changes
        return $process->getExitCode() !== 0;
    }

    /**
     * Check for unstaged changes.
     */
    private function hasUnstagedChanges(): bool
    {
        $process = Process::fromShellCommandline('git diff --quiet');
        $process->run();

        // Process will be successful when there's no changes
        return $process->getExitCode() !== 0;
    }

    /**
     * Stage all changes.
     */
    private function stageAllChanges(): void
    {
        $process = Process::fromShellCommandline('git add .');
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info('All changes have been staged.');
    }

    /**
     * Get the diff of staged changes limited by the maxDiffLines.
     */
    private function getLimitedDiff(): string
    {
        $command = "git diff --staged | grep -v 'warning' | grep -Ev 'composer\.lock|\.env'";

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $diff = $process->getOutput();
        $diffLines = explode("\n", $diff);

        $maxDiffLines = getenv('OPENAI_MODEL') ? match (getenv('OPENAI_MODEL')) {
            'gpt-3.5-turbo' => 400,
            'gpt-4' => 800,
            'gpt-4-32k' => 3200,
        } : 400;

        $limitedDiffLines = array_slice($diffLines, 0, $maxDiffLines);
        $limitedDiff = implode("\n", $limitedDiffLines);

        // Add a note if the diff was truncated
        if (count($diffLines) > $maxDiffLines) {
            $limitedDiff .= "\n\n... Diff truncated due to length";
        }

        return $limitedDiff;
    }

    /**
     * Create an AI prompt for commit message generation.
     */
    private function createAiPrompt(string $commitChanges): string
    {
        $prompt = 'Based on the following line-by-line changes in a commit, please generate an informative commit title and description';
        $prompt .= "\n(max two or three lines of description to not exceed the model max token limitation):";
        $prompt .= "\nCommit changes:";
        $prompt .= "\n{$commitChanges}";
        $prompt .= "\nFormat your response as follows:";
        $prompt .= "\nCommit title: [Generated commit title]";
        $prompt .= "\nCommit description: [Generated commit description]";

        return $prompt;
    }

    /**
     * Fetch the AI-generated content.
     *
     * @throws RequestException
     */
    private function fetchAiGeneratedContent(string $prompt): array
    {
        $this->info('Generating commit message with AI, please wait...');

        $generatedText = $this->openAi->execute($prompt, 100);

        preg_match("/Commit title: (.+)\nCommit description: (.+)/s", $generatedText, $matches);

        return [
            'title' => $matches[1] ?? '',
            'description' => $matches[2] ?? '',
        ];
    }

    /**
     * Commit the changes.
     */
    private function commit(array $commitDetails): void
    {
        $this->line("<bg=blue;options=bold>Title</>\n<fg=Cyan>{$commitDetails['title']}</>");
        $this->line("<bg=blue;options=bold>Description</>\n<fg=Cyan>{$commitDetails['description']}</>");

        if ($this->confirm('Do you wish to commit these changes?')) {
            $process = Process::fromShellCommandline('git commit -m "'.$commitDetails['title'].'" -m "'.$commitDetails['description'].'"');
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->info('Changes committed successfully.');
        }
    }
}
