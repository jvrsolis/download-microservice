<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\Services\DownloadService;

class PartialDownloadCommand extends Command
{

    /**
     * The max size to download.
     * 
     * @var string
     */
    const MAXSIZE = '1G';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download:partial 
                            {--s|sourceUrl=http://a10b57dd.bwtest-aws.pravala.com/384MB.jar : The source url.}
                            {--d|downloadSize=4mb : The amount of the file to retrieve.}
                            {--c|chunkSize=1mb : The size of the each request.}
                            {--p|destinationPath= : The output fullname of the downloaded file.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Request a file to partially download from any site with the option to resume downloading at any point in time.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function handle()
    {
        /* Set and obtain initial parameters */

        ini_set("memory_limit", self::MAXSIZE);

        $sourceUrl = $this->option('sourceUrl');

        $downloadSize = $this->option('downloadSize');

        $chunkSize = $this->option('chunkSize');

        $destinationPath = $this->option('destinationPath');

        $downloadService = app(DownloadService::class);

        $downloadService->buildRangeRequest($sourceUrl, $downloadSize, $chunkSize, $destinationPath);

        $this->info('Download service ready.');

        /* Start download */

        $this->downloadUsing($downloadService);
    }

    /**
     * Execute the download using the provided download service.
     *
     * @param \App\Services\DownloadService $downloadService
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function downloadUsing(DownloadService $downloadService)
    {
        $counter = 0;

        $this->comment('Download service starting...');

        $length = $downloadService->contentLength();

        if ($length) {
            $bar = $this->output->createProgressBar($length);

            if ($downloadService->currentSize() > 0) {
                $bar->start();
                $bar->advance($downloadService->currentSize());
            }
        } else {
            $bar = $this->output->createProgressBar($downloadService->numberOfRequests());
            $bar->start();
        }

        while ($downloadService->numberOfRequests() > $counter) {
            $from = $downloadService->currentSize();
            $to = $downloadService->currentSize() + $downloadService->chunkSize()->numberOfBytes();

            try {
                $response = $downloadService->requestRange($from, $to);

                $content = $response->getBody();

                $downloadService->writeToFile($content);
            } catch (\Exception $e) {

                if (method_exists($e, 'getResponse')) {
                    $response = $e->getResponse();

                    if ($response->getStatusCode() === 416) {
                        $this->line("");
                        $this->info('Status Code: 416');
                        $this->warn('Out of bounds ranges.');
                        break;
                    }
                }

                $this->task("Chunk Downloaded", function () {
                    return false;
                });

                $this->error($e->getMessage());
            }

            $this->line("");
            $this->task("Chunk Downloaded", function () {
                return true;
            });

            $bar->advance($downloadService->chunkSize()->numberOfBytes());

            $counter++;
        }

        if ($length === $downloadService->currentSize()) {
            $bar->finish();

            $this->task("File Downloaded", function () {
                return true;
            });
        }

        $this->line("");
        $this->comment("Download process completed");

        $headers = ['Bytes Downloaded', 'Percent Downloaded', 'Bytes Remaining', 'Percent Remaining', 'Total Bytes'];
        $this->table($headers, [[
            $downloadService->currentSize(),
            $downloadService->percentDownloaded() ?? 'Unknown Source Size.',
            $downloadService->remainingSize() ?? 'Unknown Source Size.',
            $downloadService->percentRemaining() ?? 'Unknown Source Size',
            $downloadService->contentLength() ?? 'Unknown Source Size.'
        ]]);

        $this->notify("Download Command", "Download process complete.");
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule) : void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
