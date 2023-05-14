<?php

namespace Salehhashemi\LaravelIntelliGit\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
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
        $process = Process::fromShellCommandline('git diff --cached');
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $diff = $process->getOutput();
        $prompt = $this->createAiPrompt($diff);

        try {
            $commitDetails = $this->fetchAiGeneratedContent($prompt);
            $this->commit($commitDetails);
        } catch (RequestException $e) {
            $this->error('Error fetching AI-generated content: '.$e->getMessage());
        }

        return 0;
    }

    /**
     * Create an AI prompt for commit message generation.
     */
    private function createAiPrompt(string $commitChanges): string
    {
        return "Based on the following line-by-line changes in a commit, please generate an informative commit title and description
        \n(max two or three lines of description to not exceed the model max token limitation):
        \nCommit changes:
        \n{$commitChanges}
        \nFormat your response as follows:
        \nCommit title: [Generated commit title]
        \nCommit description: [Generated commit description]";
    }

    /**
     * Fetch the AI-generated content.
     *
     * @throws RequestException
     */
    private function fetchAiGeneratedContent(string $prompt): array
    {
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
        $this->info("Title: {$commitDetails['title']}\nDescription:\n{$commitDetails['description']}");

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
