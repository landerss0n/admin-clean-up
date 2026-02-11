<?php
/**
 * Image Optimization Class
 *
 * Resizes large images and converts to WebP/AVIF on upload
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Image_Optimization {

    /**
     * Media options
     *
     * @var array
     */
    private $media_options = [];

    /**
     * Constructor
     */
    public function __construct() {
        $options = WP_Clean_Up::get_options();
        $this->media_options = isset( $options['media'] ) ? $options['media'] : [];

        $resize  = ! empty( $this->media_options['resize_on_upload'] );
        $convert = ! empty( $this->media_options['convert_on_upload'] );

        if ( $resize || $convert ) {
            add_filter( 'wp_handle_upload', [ $this, 'optimize_uploaded_image' ] );
            add_filter( 'wp_handle_sideload', [ $this, 'optimize_uploaded_image' ] );
        }

        if ( $resize ) {
            add_filter( 'big_image_size_threshold', [ $this, 'adjust_big_image_threshold' ] );
        }
    }

    /**
     * Optimize an uploaded image: resize and/or convert format.
     *
     * @param array $params Upload data with keys 'file', 'url', 'type'.
     * @return array Modified upload data.
     */
    public function optimize_uploaded_image( $params ) {
        // Only process supported image types
        $supported_types = [ 'image/jpeg', 'image/png', 'image/webp' ];
        if ( ! isset( $params['type'] ) || ! in_array( $params['type'], $supported_types, true ) ) {
            return $params;
        }

        $file = $params['file'];
        if ( ! file_exists( $file ) ) {
            return $params;
        }

        $editor = wp_get_image_editor( $file );
        if ( is_wp_error( $editor ) ) {
            return $params;
        }

        $changed = false;

        // Step 1: Resize if enabled and image exceeds max dimensions
        if ( ! empty( $this->media_options['resize_on_upload'] ) ) {
            $max_width  = isset( $this->media_options['resize_max_width'] ) ? absint( $this->media_options['resize_max_width'] ) : 2560;
            $max_height = isset( $this->media_options['resize_max_height'] ) ? absint( $this->media_options['resize_max_height'] ) : 2560;

            $size = $editor->get_size();
            if ( $size['width'] > $max_width || $size['height'] > $max_height ) {
                $resized = $editor->resize( $max_width, $max_height );
                if ( ! is_wp_error( $resized ) ) {
                    $changed = true;
                }
            }
        }

        // Step 2: Convert format if enabled
        if ( ! empty( $this->media_options['convert_on_upload'] ) ) {
            $target_format = isset( $this->media_options['convert_format'] ) ? $this->media_options['convert_format'] : 'webp';
            $target_mime   = 'image/' . $target_format;

            // Don't convert if already in target format
            if ( $params['type'] !== $target_mime ) {
                $support = self::get_server_format_support();
                if ( ! empty( $support[ $target_format ] ) ) {
                    $quality = isset( $this->media_options['convert_quality'] ) ? absint( $this->media_options['convert_quality'] ) : 82;
                    $editor->set_quality( $quality );

                    // Build new file path with target extension
                    $pathinfo = pathinfo( $file );
                    $dir      = $pathinfo['dirname'];
                    $filename = $pathinfo['filename'] . '.' . $target_format;
                    $new_file = trailingslashit( $dir ) . wp_unique_filename( $dir, $filename );

                    $saved = $editor->save( $new_file, $target_mime );
                    if ( ! is_wp_error( $saved ) ) {
                        // Delete original file
                        wp_delete_file( $file );

                        // Update params with new file info
                        $params['file'] = $saved['path'];
                        $params['type'] = $saved['mime-type'];
                        $params['url']  = str_replace(
                            wp_basename( $file ),
                            wp_basename( $saved['path'] ),
                            $params['url']
                        );
                        return $params;
                    }
                    // Conversion failed — fall through to save resize-only if needed
                }
            }
        }

        // Save resize-only changes (no format conversion, or conversion failed/skipped)
        if ( $changed ) {
            $saved = $editor->save( $file );
            if ( ! is_wp_error( $saved ) ) {
                $params['file'] = $saved['path'];
                $params['type'] = $saved['mime-type'];
            }
        }

        return $params;
    }

    /**
     * Adjust the big image size threshold to match our max dimensions.
     *
     * Prevents WordPress from creating a duplicate '-scaled' file when we
     * already resize to the same (or smaller) dimensions.
     *
     * @param int $threshold Default threshold in pixels.
     * @return int Adjusted threshold.
     */
    public function adjust_big_image_threshold( $threshold ) {
        $max_width  = isset( $this->media_options['resize_max_width'] ) ? absint( $this->media_options['resize_max_width'] ) : 2560;
        $max_height = isset( $this->media_options['resize_max_height'] ) ? absint( $this->media_options['resize_max_height'] ) : 2560;

        return max( $max_width, $max_height );
    }

    /**
     * Check server support for WebP and AVIF.
     *
     * @return array Associative array with 'webp' and 'avif' boolean values.
     */
    public static function get_server_format_support() {
        return [
            'webp' => wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ),
            'avif' => wp_image_editor_supports( [ 'mime_type' => 'image/avif' ] ),
        ];
    }
}
