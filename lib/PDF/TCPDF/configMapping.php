<?php
return $configOptions = [
    'page_size' => $options['page_size'] ?? 'A4',
    'orientation' => $options['orientation'] ?? 'P',
    'unit' => $options['unit'] ?? 'mm',
    'default_font_size' => $options['fontSize'] ?? 11,
    'default_font' => $options['font'] ?? 'DejaVuSansCondensed',
    'margin_left' => $options['margin_left'] ?? 15,
    'margin_right' => $options['margin_right'] ?? 15,
    'margin_top' => $options['margin_top'] ?? 16,
    'margin_bottom' => $options['margin_bottom'] ?? 16,
    'margin_header' => $options['margin_header'] ?? 9,
    'margin_footer' => $options['margin_footer'] ?? 9,
    'image_scale' => $options['image_scale'] ?? 1.33,
];
