<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class PortraitPaymentScreenshot implements Rule
{
    /** Minimum width in pixels. */
    public const MIN_WIDTH = 300;

    /** Minimum height in pixels. */
    public const MIN_HEIGHT = 500;

    /** Minimum file size in bytes (15 KB). */
    public const MIN_BYTES = 15_360;

    /** Reason the image was rejected (used to build the error message). */
    private string $reason = '';

    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  \Illuminate\Http\UploadedFile|mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $this->reason = 'not_readable';
            return false;
        }

        // File-size lower bound
        if ($value->getSize() < self::MIN_BYTES) {
            $this->reason = 'too_small';
            return false;
        }

        // Try to read image dimensions
        $imagePath = $value->getRealPath();
        $imageInfo = @getimagesize($imagePath);

        if ($imageInfo === false || $imageInfo[0] === 0 || $imageInfo[1] === 0) {
            $this->reason = 'unreadable';
            return false;
        }

        [$width, $height] = $imageInfo;

        // Portrait orientation: height must be strictly greater than width
        if ($height <= $width) {
            $this->reason = 'landscape';
            return false;
        }

        // Minimum resolution
        if ($width < self::MIN_WIDTH || $height < self::MIN_HEIGHT) {
            $this->reason = "low_res:{$width}x{$height}";
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return match (true) {
            str_starts_with($this->reason, 'too_small')  =>
                'The file is too small to be a valid screenshot. Please upload a full GCash receipt (minimum 15 KB).',
            str_starts_with($this->reason, 'landscape')  =>
                'Please upload a vertical (portrait) phone screenshot of your GCash receipt. Landscape / wide images are not accepted.',
            str_starts_with($this->reason, 'low_res')    =>
                'The screenshot resolution is too low. Please upload a full-size GCash receipt screenshot (at least ' . self::MIN_WIDTH . '×' . self::MIN_HEIGHT . ' px).',
            default =>
                'The payment receipt could not be verified. Please upload a clear, vertical GCash screenshot (JPG, PNG, or WebP, min ' . self::MIN_WIDTH . '×' . self::MIN_HEIGHT . ' px).',
        };
    }
}
