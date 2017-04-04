<?php
/**
 * @file
 * Contains \Drupal\cpf\Plugin\Field\FieldType\CpfItem.
 */

namespace Drupal\cpf\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\cpf\CpfItemInterface;
use Drupal\cpf\Plugin\Validation\Constraint\CpfUniqueConstraint;
use Drupal\cpf\Plugin\Validation\Constraint\CpfValueConstraint;

/**
 * Plugin implementation of the 'cpf' field type.
 *
 * @FieldType(
 *   id = "cpf",
 *   label = @Translation("CPF"),
 *   description = @Translation("This field stores a CPF number"),
 *   default_widget = "cpf_with_mask",
 *   default_formatter = "cpf_mask"
 * )
 */
class CpfItem extends FieldItemBase {
   /**
   * Specifies whether the field has only unique values..
   */
  const UNIQUE_VALUES = 0x01;

  /**
   * Specifies whether the field can have equal values.
   */
  const SAME_VALUES = 0x10;

  public $cpf_service = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
    $this->cpf_service = \Drupal::service('cpf');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'unique' => static::SAME_VALUES,
      'ignore_bundle' => 0,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];

    $element['unique'] = [
      '#type' => 'radios',
      '#title' => t('Allow only unique values'),
      '#default_value' => $this->getSetting('unique'),
      '#options' => [
        static::SAME_VALUES => $this->t('no'),
        static::UNIQUE_VALUES => $this->t('yes'),
      ],
      '#disabled' => $has_data,
    ];

    $element['ignore_bundle'] = [
      '#type' => 'checkbox',
      '#title' => t('Ignore bundle'),
      '#default_value' => $this->getSetting('ignore_bundle'),
      '#description' => $this->t('If this option is selected, validation of unique values will ignore the entity type (bundle).'),
      '#states' => [
        'visible' => [
          ':input[name="settings[unique]"]' => [
            'value' => static::UNIQUE_VALUES,
          ],
        ],
      ],
      '#disabled' => $has_data,
    ];

    $element += parent::storageSettingsForm($form, $form_state, $has_data);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $unique = $this->getSetting('unique');

    $value_constraints = [];
    $value_constraints['CpfValue'] = [];

    if ($unique == static::UNIQUE_VALUES) {
      $value_constraints['CpfUnique'] = [
        'fieldDefinition' => $this->getFieldDefinition(),
        'ignoreBundle' => $this->getSetting('ignore_bundle'),
        'entity' => $this->getEntity(),
      ];
    }

    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints[] = $constraint_manager->create('ComplexData', [
      'value' => $value_constraints,
    ]);

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['value'] = \Drupal::service('cpf')->generate();
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    $value = $this->cpf_service->digits($value);
    return empty($value);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Remove all characters except numbers.
    $values['value'] = $this->cpf_service->digits($values['value']);
    parent::setValue($values, $notify);
  }
}
