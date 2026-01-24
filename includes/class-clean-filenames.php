<?php
/**
 * Clean Filenames Class
 *
 * Cleans filenames when uploading to media library
 *
 * @package WP_Clean_Up
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Clean_Up_Clean_Filenames {

    /**
     * Default image mime types
     */
    private $image_mime_types = [
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/tiff',
        'image/avif',
        'image/webp',
        'image/svg+xml',
        'image/bmp',
        'image/heic',
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $options = WP_Clean_Up::get_options();
        $media_options = isset( $options['media'] ) ? $options['media'] : [];

        if ( ! empty( $media_options['clean_filenames'] ) ) {
            add_filter( 'wp_handle_upload_prefilter', [ $this, 'clean_filename_on_upload' ] );
            add_filter( 'wp_handle_sideload_prefilter', [ $this, 'clean_filename_on_upload' ] );
            add_action( 'add_attachment', [ $this, 'restore_original_title' ] );
        }
    }

    /**
     * Clean filename when uploading
     *
     * @param array $file File data including name, type, tmp_name, error, size
     * @return array Modified file data
     */
    public function clean_filename_on_upload( $file ) {
        $options = WP_Clean_Up::get_options();
        $media_options = isset( $options['media'] ) ? $options['media'] : [];
        $file_types = isset( $media_options['clean_filenames_types'] ) ? $media_options['clean_filenames_types'] : 'all';

        // Check if we should clean this file type
        if ( 'images' === $file_types && ! in_array( $file['type'], $this->image_mime_types, true ) ) {
            return $file;
        }

        // Get file info
        $pathinfo = pathinfo( $file['name'] );
        $filename = isset( $pathinfo['filename'] ) ? $pathinfo['filename'] : '';
        $extension = isset( $pathinfo['extension'] ) ? strtolower( $pathinfo['extension'] ) : '';

        if ( empty( $filename ) ) {
            return $file;
        }

        // Store original filename for attachment title
        set_transient( '_admin_clean_up_original_filename', $filename, 60 );

        // Clean the filename
        $cleaned = $this->clean_filename( $filename );

        // Ensure we have a valid filename
        if ( empty( $cleaned ) ) {
            $cleaned = 'file-' . time();
        }

        // Rebuild filename with extension
        $file['name'] = $cleaned . '.' . $extension;

        return $file;
    }

    /**
     * Clean a filename string
     *
     * @param string $filename The filename to clean
     * @return string Cleaned filename
     */
    private function clean_filename( $filename ) {
        // Character replacements for various languages
        $replacements = [
            // Swedish/Nordic
            'å' => 'a', 'Å' => 'a', 'ä' => 'a', 'Ä' => 'a', 'ö' => 'o', 'Ö' => 'o',
            'æ' => 'ae', 'Æ' => 'ae', 'ø' => 'o', 'Ø' => 'o',

            // German
            'ü' => 'u', 'Ü' => 'u', 'ß' => 'ss',

            // French/Spanish/Portuguese
            'é' => 'e', 'É' => 'e', 'è' => 'e', 'È' => 'e', 'ê' => 'e', 'Ê' => 'e', 'ë' => 'e', 'Ë' => 'e',
            'á' => 'a', 'Á' => 'a', 'à' => 'a', 'À' => 'a', 'â' => 'a', 'Â' => 'a', 'ã' => 'a', 'Ã' => 'a',
            'í' => 'i', 'Í' => 'i', 'ì' => 'i', 'Ì' => 'i', 'î' => 'i', 'Î' => 'i', 'ï' => 'i', 'Ï' => 'i',
            'ó' => 'o', 'Ó' => 'o', 'ò' => 'o', 'Ò' => 'o', 'ô' => 'o', 'Ô' => 'o', 'õ' => 'o', 'Õ' => 'o',
            'ú' => 'u', 'Ú' => 'u', 'ù' => 'u', 'Ù' => 'u', 'û' => 'u', 'Û' => 'u',
            'ñ' => 'n', 'Ñ' => 'n', 'ç' => 'c', 'Ç' => 'c',
            'ý' => 'y', 'Ý' => 'y', 'ÿ' => 'y', 'Ÿ' => 'y',

            // Polish
            'ą' => 'a', 'Ą' => 'a', 'ć' => 'c', 'Ć' => 'c', 'ę' => 'e', 'Ę' => 'e',
            'ł' => 'l', 'Ł' => 'l', 'ń' => 'n', 'Ń' => 'n', 'ś' => 's', 'Ś' => 's',
            'ź' => 'z', 'Ź' => 'z', 'ż' => 'z', 'Ż' => 'z',

            // Czech/Slovak
            'č' => 'c', 'Č' => 'c', 'ď' => 'd', 'Ď' => 'd', 'ě' => 'e', 'Ě' => 'e',
            'ň' => 'n', 'Ň' => 'n', 'ř' => 'r', 'Ř' => 'r', 'š' => 's', 'Š' => 's',
            'ť' => 't', 'Ť' => 't', 'ů' => 'u', 'Ů' => 'u', 'ž' => 'z', 'Ž' => 'z',

            // Hungarian
            'ő' => 'o', 'Ő' => 'o', 'ű' => 'u', 'Ű' => 'u',

            // Turkish
            'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ş' => 's', 'Ş' => 's',

            // Icelandic
            'ð' => 'd', 'Ð' => 'd', 'þ' => 'th', 'Þ' => 'th',

            // Greek
            'α' => 'a', 'Α' => 'a', 'β' => 'v', 'Β' => 'v', 'γ' => 'g', 'Γ' => 'g',
            'δ' => 'd', 'Δ' => 'd', 'ε' => 'e', 'Ε' => 'e', 'ζ' => 'z', 'Ζ' => 'z',
            'η' => 'i', 'Η' => 'i', 'θ' => 'th', 'Θ' => 'th', 'ι' => 'i', 'Ι' => 'i',
            'κ' => 'k', 'Κ' => 'k', 'λ' => 'l', 'Λ' => 'l', 'μ' => 'm', 'Μ' => 'm',
            'ν' => 'n', 'Ν' => 'n', 'ξ' => 'x', 'Ξ' => 'x', 'ο' => 'o', 'Ο' => 'o',
            'π' => 'p', 'Π' => 'p', 'ρ' => 'r', 'Ρ' => 'r', 'σ' => 's', 'Σ' => 's',
            'ς' => 's', 'τ' => 't', 'Τ' => 't', 'υ' => 'y', 'Υ' => 'y', 'φ' => 'f',
            'Φ' => 'f', 'χ' => 'ch', 'Χ' => 'ch', 'ψ' => 'ps', 'Ψ' => 'ps', 'ω' => 'o', 'Ω' => 'o',
            'ά' => 'a', 'Ά' => 'a', 'έ' => 'e', 'Έ' => 'e', 'ή' => 'i', 'Ή' => 'i',
            'ί' => 'i', 'Ί' => 'i', 'ό' => 'o', 'Ό' => 'o', 'ύ' => 'y', 'Ύ' => 'y',
            'ώ' => 'o', 'Ώ' => 'o', 'ϊ' => 'i', 'Ϊ' => 'i', 'ϋ' => 'y', 'Ϋ' => 'y',

            // Russian/Cyrillic
            'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v',
            'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e',
            'ё' => 'yo', 'Ё' => 'yo', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z',
            'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k',
            'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n',
            'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r',
            'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u',
            'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c',
            'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'shch', 'Щ' => 'shch',
            'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '',
            'э' => 'e', 'Э' => 'e', 'ю' => 'yu', 'Ю' => 'yu', 'я' => 'ya', 'Я' => 'ya',

            // Ukrainian
            'є' => 'ye', 'Є' => 'ye', 'і' => 'i', 'І' => 'i', 'ї' => 'yi', 'Ї' => 'yi',
            'ґ' => 'g', 'Ґ' => 'g',

            // Special characters
            '&' => '-and-',
            '@' => '-at-',
            '#' => '',
            '$' => '',
            '%' => '',
            '^' => '',
            '*' => '',
            '(' => '',
            ')' => '',
            '[' => '',
            ']' => '',
            '{' => '',
            '}' => '',
            '|' => '',
            '\\' => '',
            '/' => '-',
            ':' => '',
            ';' => '',
            '"' => '',
            "'" => '',
            '<' => '',
            '>' => '',
            ',' => '',
            '.' => '-',
            '?' => '',
            '!' => '',
            '`' => '',
            '~' => '',
            '=' => '',
            '+' => '',
            '×' => 'x',
            '–' => '-',
            '—' => '-',
            "\u{2018}" => '', // '
            "\u{2019}" => '', // '
            "\u{201C}" => '', // "
            "\u{201D}" => '', // "
            '…' => '',
            '©' => '',
            '®' => '',
            '™' => '',
            '°' => '',
            '€' => 'eur',
            '£' => 'gbp',
            '¥' => 'yen',
            '¢' => 'c',

            // Whitespace
            ' ' => '-',
            "\t" => '-',
            "\n" => '-',
            "\r" => '-',
            '_' => '-',
            '%20' => '-',
        ];

        // Apply character replacements
        $cleaned = str_replace( array_keys( $replacements ), array_values( $replacements ), $filename );

        // Use WordPress remove_accents for any remaining accented characters
        $cleaned = remove_accents( $cleaned );

        // Convert to lowercase
        $cleaned = strtolower( $cleaned );

        // Remove any remaining non-alphanumeric characters except dashes
        $cleaned = preg_replace( '/[^a-z0-9\-]/', '', $cleaned );

        // Replace multiple consecutive dashes with single dash
        $cleaned = preg_replace( '/-+/', '-', $cleaned );

        // Trim dashes from start and end
        $cleaned = trim( $cleaned, '-' );

        return $cleaned;
    }

    /**
     * Restore original filename as attachment title
     *
     * @param int $attachment_id Attachment post ID
     */
    public function restore_original_title( $attachment_id ) {
        $original_filename = get_transient( '_admin_clean_up_original_filename' );

        if ( $original_filename ) {
            // Make the title more readable by replacing dashes/underscores with spaces
            $title = str_replace( [ '-', '_' ], ' ', $original_filename );
            $title = ucwords( $title );

            wp_update_post( [
                'ID'         => $attachment_id,
                'post_title' => $title,
            ] );

            delete_transient( '_admin_clean_up_original_filename' );
        }
    }
}
