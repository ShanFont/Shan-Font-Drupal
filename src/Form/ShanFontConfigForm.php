<?php

namespace Drupal\shanfont\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configure Shan Font settings.
 */
class ShanFontConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['shanfont.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shanfont_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('shanfont.settings');
    $module_path = \Drupal::service('extension.list.module')->getPath('shanfont');
    
    // Header section with logo and info
    $form['header'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['shanfont-header']],
    ];
    
    $form['header']['logo'] = [
      '#theme' => 'image',
      '#uri' => $module_path . '/assets/ShanFont.webp',
      '#alt' => $this->t('Shan Font Logo'),
      '#attributes' => ['class' => ['shanfont-logo']],
    ];
    
    $form['header']['title'] = [
      '#markup' => '<h2 class="shanfont-title">' . $this->t('Shan Font') . '</h2>',
    ];
    
    $form['header']['website'] = [
      '#markup' => '<p class="shanfont-website"><a href="https://shanfont.com" target="_blank" rel="noopener">' . $this->t('www.shanfont.com') . '</a></p>',
    ];
    
    $form['header']['developer'] = [
      '#markup' => '<p class="shanfont-developer">' . $this->t('Developed by TaiDev') . '</p>',
    ];

    // Font mode selection
    $form['font_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Font Mode'),
      '#description' => $this->t('လိူၵ်ႈၾွၼ်ႉတႃႇဝႅပ်ႉ'),
      '#options' => [
        'theme_defaults' => $this->t('Theme Defaults (ၸႂ်ႉၾွၼ်ႉဢၼ်ၵိုၵ်းမႃးၸွမ် Theme)'),
        'quick_setup' => $this->t('Quick Setup (ၸႂ်ႉၾွၼ်ႉ Shan)'),
        'custom_selection' => $this->t('Custom Selection (လိူၵ်ႈၾွၼ်ႉဢၼ်ၶႂ်ႈၸႂ်ႉ)'),
      ],
      '#default_value' => $config->get('font_mode') ?: 'quick_setup',
      '#required' => TRUE,
    ];

    // Description for each mode
    $form['mode_descriptions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['shanfont-mode-descriptions']],
    ];
    
    $form['mode_descriptions']['theme_defaults_desc'] = [
      '#type' => 'container',
      '#markup' => '<div class="description theme-defaults-desc">' . 
        $this->t('ၸႂ်ႉၾွၼ်ႉဢၼ်ၵိုၵ်းမႃးၸွမ်း theme (default theme fonts)') . 
        '</div>',
      '#states' => [
        'visible' => [
          ':input[name="font_mode"]' => ['value' => 'theme_defaults'],
        ],
      ],
    ];
    
    $form['mode_descriptions']['quick_setup_desc'] = [
      '#type' => 'container',
      '#markup' => '<div class="description quick-setup-desc">' . 
        $this->t('ၸႂ်ႉၾွၼ်ႉ Shan ဢၼ်သႂ်ႇပႃးတူဝ်လိၵ်ႈမၢင်၊ တူဝ်လိၵ်ႈၼႃ လႄႈ တူဝ်လိၵ်ႈၵိူင်း') . 
        '</div>',
      '#states' => [
        'visible' => [
          ':input[name="font_mode"]' => ['value' => 'quick_setup'],
        ],
      ],
    ];
    
    $form['mode_descriptions']['custom_selection_desc'] = [
      '#type' => 'container',
      '#markup' => '<div class="description custom-selection-desc">' . 
        $this->t('လိူၵ်ႈၾွၼ်ႉ Shan ဢၼ်ၶႂ်ႈၸႂ်ႉတႃႇဝႅပ်ႉ မိူၼ်ၼင်ႇတူဝ်လိၵ်ႈမၢင်၊ တူဝ်လိၵ်ႈၼႃ လႄႈ တူဝ်လိၵ်ႈၵိူင်း') . 
        '</div>',
      '#states' => [
        'visible' => [
          ':input[name="font_mode"]' => ['value' => 'custom_selection'],
        ],
      ],
    ];

    // Custom font selection
    $form['custom_fonts'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Select Font Variants'),
      '#states' => [
        'visible' => [
          ':input[name="font_mode"]' => ['value' => 'custom_selection'],
        ],
      ],
    ];

    $font_options = [
      'shan_thin' => $this->t('Shan Thin'),
      'shan_thin_italic' => $this->t('Shan Thin Italic'),
      'shan_regular' => $this->t('Shan Regular'),
      'shan_italic' => $this->t('Shan Italic'),
      'shan_bold' => $this->t('Shan Bold'),
      'shan_bold_italic' => $this->t('Shan Bold Italic'),
      'shan_black' => $this->t('Shan Black'),
      'shan_black_italic' => $this->t('Shan Black Italic'),
    ];

    $selected_fonts = $config->get('selected_fonts') ?: [];
    
    foreach ($font_options as $key => $label) {
      $form['custom_fonts'][$key] = [
        '#type' => 'checkbox',
        '#title' => $label,
        '#default_value' => isset($selected_fonts[$key]) ? $selected_fonts[$key] : FALSE,
      ];
    }

    $form['#attached']['library'][] = 'shanfont/admin-styles';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('shanfont.settings');
    
    $config->set('font_mode', $form_state->getValue('font_mode'));
    
    // Save selected fonts for custom selection mode
    $selected_fonts = [];
    $font_options = [
      'shan_thin', 'shan_thin_italic', 'shan_regular', 'shan_italic',
      'shan_bold', 'shan_bold_italic', 'shan_black', 'shan_black_italic'
    ];
    
    foreach ($font_options as $font) {
      $selected_fonts[$font] = $form_state->getValue($font) ? TRUE : FALSE;
    }
    
    $config->set('selected_fonts', $selected_fonts);
    $config->save();
    
    // Clear cache to apply changes
    drupal_flush_all_caches();
    
    $this->messenger()->addMessage($this->t('Shan Font settings have been saved.'));
    
    parent::submitForm($form, $form_state);
  }

}