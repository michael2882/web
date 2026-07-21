<?php
// === Shortcode [lens_compare ...] con clip-path (sin escalado de imágenes) ===
// Uso: [lens_compare left="URL" right="URL" left_label="TRANSPARENTES" right_label="FOTOCROMÁTICOS" start="52" ratio="16/9"]

add_action('init', function () {
  add_shortcode('lens_compare', 'pd_lens_compare_sc');
});

add_action('wp_enqueue_scripts', function () {
  wp_register_style(
    'pd-lens-compare',
    get_stylesheet_directory_uri() . '/css/lens-compare.css',
    [],
    '1.2'
  );
  wp_register_script(
    'pd-lens-compare',
    get_stylesheet_directory_uri() . '/js/lens-compare.js',
    [],
    '1.2',
    true
  );
});

function pd_lens_compare_sc($atts) {
  $a = shortcode_atts([
    'left'        => '',
    'right'       => '',
    'left_label'  => '',
    'right_label' => '',
    'start'       => '50',   // 0–100 o 0–1
    'ratio'       => '16/9', // CSS aspect-ratio
  ], $atts, 'lens_compare');

  $left_raw  = trim((string)$a['left']);
  $right_raw = trim((string)$a['right']);

  if ($left_raw === '' || $right_raw === '') {
    return '<div style="color:#c00;font-weight:600">[lens_compare] falta el atributo left o right.</div>';
  }

  $left_safe  = esc_url($left_raw);
  $right_safe = esc_url($right_raw);
  if ($left_safe === '')  { $left_safe  = esc_attr($left_raw); }
  if ($right_safe === '') { $right_safe = esc_attr($right_raw); }

  $left_label  = sanitize_text_field($a['left_label']);
  $right_label = sanitize_text_field($a['right_label']);

  $ratio = preg_replace('#[^0-9/\.]#', '', (string)$a['ratio']);
  if ($ratio === '') { $ratio = '16/9'; }

  $start_raw = str_replace(',', '.', (string)$a['start']);
  $start_val = is_numeric($start_raw) ? (float)$start_raw : 50;
  $start = ($start_val <= 1) ? $start_val * 100 : $start_val;
  $start = max(0, min(100, $start));

  wp_enqueue_style('pd-lens-compare');
  wp_enqueue_script('pd-lens-compare');

  $uid = 'lc_' . wp_generate_uuid4();

  // Usamos clip-path en la imagen superior; el "right inset" será (100 - start)%
  $right_inset = 100 - $start;

  $html  = '';
  $html .= '<div id="'.esc_attr($uid).'" class="lc" data-start="'.esc_attr($start).'" style="--lc-aspect: '.esc_attr($ratio).';">';
  $html .= '  <figure class="lc-inner">';
  $html .= '    <img class="lc-img lc-img--base" src="'.$right_safe.'" alt="'.esc_attr($right_label ?: 'Después').'" loading="eager">';
  $html .= '    <img class="lc-img lc-img--top"  src="'.$left_safe.'"  alt="'.esc_attr($left_label  ?: 'Antes').'"   loading="eager" style="clip-path: inset(0 '.esc_attr($right_inset).'%' .' 0 0);">';
  $html .= '    <div class="lc-handle" style="left: '.esc_attr($start).'%;"><span class="lc-knob" aria-hidden="true">↔</span></div>';
  if ($left_label  !== '') { $html .= '    <div class="lc-label lc-label--left">'.esc_html($left_label).'</div>'; }
  if ($right_label !== '') { $html .= '    <div class="lc-label lc-label--right">'.esc_html($right_label).'</div>'; }
  $html .= '  </figure>';
  $html .= '</div>';

  return shortcode_unautop($html);
}

