<?php

/**
 * @file
 * Contains \Drupal\cpf\Plugin\Validation\Constraint\CpfUniqueConstraint.
 */

namespace Drupal\cpf\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Drupal\cpf\Plugin\Validation\Constraint\CpfUniqueConstraintValidator;

/**
 * Supports validating CPF numbers.
 *
 * @Constraint(
 *   id = "CpfUnique",
 *   label = @Translation("CPF Value", context = "Validation")
 * )
 */
class CpfUniqueConstraint extends Constraint {
  public $entity = null;
  public $fieldDefinition = '';
  public $ignoreBundle = 0;
  public $message = 'The CPF number %value already exists. Enter a unique number.';

  public function __construct($options = null) {
    parent::__construct($options);
  }

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return '\Drupal\cpf\Plugin\Validation\Constraint\CpfUniqueConstraintValidator';
  }
}
