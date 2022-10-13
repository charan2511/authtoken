<?php

namespace Drupal\custom_authtoken\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\user\Entity\User;

/**
 * Class GenerateAuthToken.
 */
class GenerateAuthToken extends FormBase {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  public $tree_array;
  public static $parent_tree;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'generate_AuthToken_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


   

   $form['text_markup'] = [
     '#type' => 'markup',
     '#markup' => '<p>'.$this->t('Generate AUTH TOKEN for all existing users').'</p>',
   ];


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $operations = [];
    $opr = '\Drupal\custom_authtoken\Form\GenerateAuthToken::userbatch';
    
    $database = \Drupal::database();
    $uids = $database->query("SELECT uid FROM users_field_data where uid > 0 ")->fetchCol();
    foreach ($uids as $key => $value) {
        $operations[] = [$opr,[$value]];
    }

    $batch = [
        'title' => t('Importing please do not exit or refresh this page ...'),
        'operations' => $operations,
        'init_message'     => t('Start Import'),
        'progress_message' => t('Processed @current out of @total.'),
        'error_message'    => t('An error occurred during processing'),
      ];
    \Drupal::messenger()->addMessage("Import finished");
    batch_set($batch);
    
  }

  public static function userbatch($uid){ 
    
    if(!empty($uid)){
      $user = User::load($uid);
      if(is_object($user)){
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $token = substr(str_shuffle($permitted_chars), 0, 32);
            $user->set('field_auth_token', [$token]);
            $user->save();
            \Drupal::messenger()->addMessage($uid);
      }
    } 
    
  }

}
