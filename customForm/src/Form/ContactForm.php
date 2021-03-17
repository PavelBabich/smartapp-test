<?php

namespace Drupal\customForm\Form;

use Drupal\contact\MessageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\RequestException;

class ContactForm extends FormBase
{
    public function getFormId()
    {
        return 'contact_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['first_name'] = [
            '#type' => 'textfield',
            '#title' => 'First Name'
        ];

        $form['last_name'] = [
            '#type' => 'textfield',
            '#title' => 'Last Name'
        ];

        $form['subject'] = [
            '#type' => 'textfield',
            '#title' => 'Subject'
        ];

        $form['message'] = [
            '#type' => 'textarea',
            '#title' => 'Message'
        ];

        $form['email'] = [
            '#type' => 'email',
            '#title' => 'Email'
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit message'
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $value = $form_state->getValue('email');

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $form_state->setErrorByName('email', $this->t('The email address %mail is not valid.', array('%mail' => $value)));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $mailManager = \Drupal::service('plugin.manager.mail');

        $firstName = $form_state->getValue('first_name');
        $lastName = $form_state->getValue('last_name');
        $email = $form_state->getValue('email');
        $params['subject'] = $form_state->getValue('subject');
        $params['message'] = $form_state->getValue('message');
        $params['email'] = $email;

        $site_email = \Drupal::config('system.site')->get('mail');

        $result = $mailManager->mail('CustomForm', 'sendMail', $site_email, 'ru', $params, NULL, $send = TRUE);

        if ($result['result'] == true) {
            \Drupal::logger('contact_form')->notice('Mail has been sent. E-mail: ' . $form_state->getValue('email'));
            $messanger = \Drupal::messenger();
            $messanger->addMessage('Email has been sent');
        }

        $url = 'https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/' . $email . '/?hapikey=f7d28331-63a1-4c7d-a94b-3acd23e11e2b';

        $data = [
            'properties' => [
                [
                    'property' => 'firstname',
                    'value' => $firstName
                ],
                [
                    'property' => 'lastname',
                    'value' => $lastName
                ]
            ]
        ];

        $json = json_encode($data, true);

        $request = \Drupal::httpClient()->post($url. '&_format=hal_json', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $json
        ]);
    }
}
