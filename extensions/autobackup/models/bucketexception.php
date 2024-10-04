<?php


class BucketException extends Exception {

    public function __construct($message = '', $data_to_log = null, $code = 4, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);

        if (BackupWorker::WRITE_LOG) {
            Log::add(4,$message, $data_to_log, "autobackup");
        }
    }
}