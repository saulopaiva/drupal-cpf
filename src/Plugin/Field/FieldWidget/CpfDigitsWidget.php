<?php

namespace Drupal\cpf\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cpf\CpfWidgetBase;

/**
 * Plugin implementation of the 'cpf_digits' widget.
 *
 * @FieldWidget(
 *   id = "cpf_digits",
 *   label = @Translation("Digits"),
 *   field_types = {
 *     "cpf"
 *   }
 * )
 */
class CpfDigitsWidget extends CpfWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
      'generator' => 0,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];

    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    $elements['generator'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable link to generate CPF numbers.'),
      '#default_value' => $this->getSetting('generator'),
      '#description' => $this->t('If enabled, a link will be added allowing you to generate a valid CPF number. <br/><strong>Note that to view the link the user must have the permission "Access the CPF number generator"</strong>.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Textfield size: @size', ['@size' => $this->getSetting('size')]);

    $placeholder = $this->getSetting('placeholder');
    if (!empty($placeholder)) {
      $summary[] = $this->t('Placeholder: @placeholder', ['@placeholder' => $placeholder]);
    }

    $generator = empty($this->getSetting('generator')) ? $this->t('no') : $this->t('yes');
    $summary[] = $this->t('Link to generate CPF numbers: @generator', ['@generator' => $generator]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element_id = $this->formElementId($element);

    $data['cpf']['mask_plugin']['elements'][$element_id] = [
      'id' => $element_id,
      'mask' => '00000000000',
    ];

    $widget = [
      '#type' => 'textfield',
      '#id' => $element_id,
      '#size' => $this->getSetting('size'),
      '#maxlength' => 11,
      '#placeholder' => $this->getSetting('placeholder'),
      '#field_name' => $this->fieldDefinition->getName(),
      '#default_value' => $value,
      '#attached' => [
        'library' => [
          'cpf/mask_plugin',
          'cpf/cpf',
        ],
        'drupalSettings' => $data,
      ],
      '#element_validate' => [
        [
          get_called_class(),
          'validateElement',
        ],
      ],
    ];

    $generator = $this->getSetting('generator');
    if ($generator && \Drupal::currentUser()->hasPermission('access cpf generator')) {
      $generator_id = 'generate-' . $element_id;
      $data['cpf']['generator']['elements'][$generator_id] = [
        'id' => $generator_id,
        'target' => $element_id,
      ];

      $widget['#attached']['drupalSettings'] = $data;
      $widget['#suffix'] = '<a id="' . $generator_id . '" href="#">' . $this->t('Generate') . '</a>';
    }

    $element['value'] = $element + $widget;
    return $element;
  }

}
