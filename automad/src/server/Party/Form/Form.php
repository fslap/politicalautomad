<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 *                      #;:#;:#;:
 *                       #;:#;:            #;:#;:#;
 *            %&                         #;: #;:;:#;:#;:#;:#
 *    %$%    %$%           #;:    #;:     #;: #;:;:#;:#;:
 *    ____________                ;:      #;:  #;: #;:#;:#;:#;
 *   /§§§§§§§§§§§|                                    ;:#;:#;:
 *   |###########/  / &/      #;:#;:#;:#;:#;:        #;:#;: #;: #;
 *   /%%%%%%%%%%/   |$$$/        #;:#;:#;:#;:#;#;:#;:;:#;:#;:
 *  /######### /   |$$$/          #;:#;:#;:#;:#;:#;:#;:#;:#;:#;:
 * /%%%%%%%%%%/   \CCC/           #;:#;:#;:#;:#;:#;:#;:#;:
 * \#########|   \\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
 *  \________/   /[[]]\  \_/   :#\:#\:#\:#\:#;:
 *   \====__/    \xxxx/              #\:#\:#;:#;:
 *   |""""""\    |yyy/                #;: #;:#;:#;: |#;:\
 *   |~~~~~~/ #   |c|                 ;:#;:\;:#;:   |#;:;:
 *   +~~~~~/  |   \t/                  #\:#;\       |##;/
 *    +~~~/   |#
 *     +~/\       \*+ ~
 *                      #
 *
 *
 *
 * https://north.sbdp.ro https://mid.sbdp.ro/ https://pacif.sbdp.ro/ https://low.sbdp.ro/
 * (c) Florian Leon Steenbuck
 *
 * Copyright (c) 2026 by Florian Leon Steenbuck
 * https://kil.ls https://fslap.de
 *
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad\Party\Form;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\System\Fetch;
use Automad\System\Mail;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Form (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Form extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	/**
	 * Prevent sending a form twice during a single request.
	 */
	private static array $handled = array();

	public function __construct() {
		parent::__construct(__DIR__, 'form');
	}

	public function context(array $config): array {
		$sectionId = $config['section_id'] ?: 'party_form';
		$fields = array();

		foreach ($config['fields'] as $index => $field) {
			if (!is_array($field)) {
				continue;
			}

			$type = (string) ($field['type'] ?? 'text');
			$name = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($field['name'] ?? '')) ?: 'field_' . $index;

			$fields[] = array(
				'name' => $name,
				'id' => $sectionId . '_' . $name,
				'label' => (string) ($field['label'] ?? $name),
				'type' => $type,
				'required' => !empty($field['required']),
				'required_attr' => !empty($field['required']) ? 'required' : '',
				'options' => array_values((array) ($field['options'] ?? array())),
				'is_textarea' => $type === 'textarea',
				'is_select' => $type === 'select',
				'is_checkbox' => $type === 'checkbox',
				'is_input' => !in_array($type, array('textarea', 'select', 'checkbox'), true)
			);
		}

		$builtin = $config['form_action'] === '' && $config['mail_to'] !== '';

		return array(
			'section_id' => $sectionId,
			'title' => $config['title'],
			'description' => $config['description'],
			'form_method' => $builtin ? 'post' : $config['form_method'],
			'form_action' => $config['form_action'],
			'submit_label' => $config['submit_label'],
			'turnstile_theme' => $config['turnstile_theme'],
			'turnstile_site_key' => $config['turnstile_site_key'],
			'fields' => $fields,
			'status' => $builtin ? $this->handleSubmission($sectionId, $fields, $config) : array(),
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Formular (Turnstile)',
			'icon' => 'ui-checks',
			'description' => 'Formular mit frei definierbaren Feldern, Cloudflare Turnstile und Mailversand über die Automad-Mailkonfiguration.',
			'fields' => array(
				self::sectionIdField('membership_form'),
				self::titleField('Mitglied werden'),
				array('name' => 'description', 'type' => 'textarea', 'label' => 'Beschreibung'),
				array(
					'name' => 'fields',
					'type' => 'list',
					'label' => 'Formularfelder',
					'itemTitle' => 'label',
					'default' => array(
						array('name' => 'firstname', 'type' => 'text', 'label' => 'Vorname', 'required' => true, 'options' => array()),
						array('name' => 'lastname', 'type' => 'text', 'label' => 'Nachname', 'required' => true, 'options' => array()),
						array('name' => 'email', 'type' => 'email', 'label' => 'E-Mail', 'required' => true, 'options' => array())
					),
					'fields' => array(
						array('name' => 'label', 'type' => 'text', 'label' => 'Beschriftung'),
						array('name' => 'name', 'type' => 'text', 'label' => 'Feldname (technisch)'),
						array('name' => 'type', 'type' => 'select', 'label' => 'Typ', 'default' => 'text', 'options' => array('text' => 'Text', 'email' => 'E-Mail', 'tel' => 'Telefon', 'date' => 'Datum', 'number' => 'Zahl', 'textarea' => 'Mehrzeilig', 'select' => 'Auswahl', 'checkbox' => 'Checkbox')),
						array('name' => 'required', 'type' => 'toggle', 'label' => 'Pflichtfeld'),
						array('name' => 'options', 'type' => 'strings', 'label' => 'Optionen (nur Auswahl, eine pro Zeile)')
					)
				),
				array('name' => 'submit_label', 'type' => 'text', 'label' => 'Text des Absende-Buttons', 'default' => 'Absenden'),
				array('name' => 'mail_to', 'type' => 'text', 'label' => 'Empfänger (E-Mail)', 'help' => 'Wenn gesetzt und keine Ziel-URL angegeben ist, wird das Formular von Automad verarbeitet und über die SMTP-Einstellungen des Dashboards versendet.'),
				array('name' => 'form_action', 'type' => 'text', 'label' => 'Alternativ: externe Ziel-URL'),
				array('name' => 'form_method', 'type' => 'select', 'label' => 'Methode', 'default' => 'post', 'options' => array('post' => 'POST', 'get' => 'GET')),
				array('name' => 'success_message', 'type' => 'text', 'label' => 'Erfolgsmeldung', 'default' => 'Vielen Dank! Ihre Nachricht wurde gesendet.'),
				array('name' => 'error_message', 'type' => 'text', 'label' => 'Fehlermeldung', 'default' => 'Bitte füllen Sie alle Pflichtfelder aus und bestätigen Sie die Sicherheitsabfrage.'),
				array('name' => 'turnstile_site_key', 'type' => 'text', 'label' => 'Turnstile Site-Key', 'help' => 'Der geheime Schlüssel wird nicht hier, sondern in der Umgebungsvariable PARTY_TURNSTILE_SECRET hinterlegt.'),
				array('name' => 'turnstile_theme', 'type' => 'select', 'label' => 'Turnstile-Theme', 'default' => 'light', 'options' => array('light' => 'Hell', 'dark' => 'Dunkel', 'auto' => 'Automatisch')),
				self::classesField('bgpurple widthcontainer')
			)
		);
	}

	/**
	 * Handle a submission of this form and return the status for the template.
	 *
	 * @param string $sectionId
	 * @param array $fields
	 * @param array $config
	 * @return array
	 */
	private function handleSubmission(string $sectionId, array $fields, array $config): array {
		if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || ($_POST['form_section_id'] ?? '') !== $sectionId) {
			return array();
		}

		if (isset(self::$handled[$sectionId])) {
			return self::$handled[$sectionId];
		}

		$error = array('error' => true, 'message' => $config['error_message']);

		// Honeypot.
		if (!empty($_POST['nickname'])) {
			return self::$handled[$sectionId] = $error;
		}

		if ($config['turnstile_site_key'] !== '' && !$this->verifyTurnstile((string) ($_POST['cf-turnstile-response'] ?? ''))) {
			return self::$handled[$sectionId] = $error;
		}

		$rows = array();
		$replyTo = null;

		foreach ($fields as $field) {
			$value = $_POST[$field['name']] ?? '';
			$value = is_string($value) ? trim($value) : '';

			if ($field['required'] && $value === '') {
				return self::$handled[$sectionId] = $error;
			}

			if ($field['type'] === 'email' && $value !== '') {
				if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
					return self::$handled[$sectionId] = $error;
				}

				$replyTo = $replyTo ?? $value;
			}

			$rows[] = '<tr><th align="left" style="padding: 4px 12px 4px 0;">' . htmlspecialchars($field['label']) . '</th><td>' . nl2br(htmlspecialchars($value)) . '</td></tr>';
		}

		$subject = strip_tags(($config['title'] ?: 'Formular') . ' – ' . $sectionId);
		$message = '<h2>' . htmlspecialchars($subject) . '</h2><table>' . join('', $rows) . '</table>';

		if (!Mail::send((string) $config['mail_to'], $subject, $message, $replyTo)) {
			return self::$handled[$sectionId] = $error;
		}

		return self::$handled[$sectionId] = array('success' => true, 'message' => $config['success_message']);
	}

	/**
	 * Verify a Cloudflare Turnstile token.
	 *
	 * @param string $token
	 * @return bool
	 */
	private function verifyTurnstile(string $token): bool {
		$secret = getenv('PARTY_TURNSTILE_SECRET') ?: (defined('PARTY_TURNSTILE_SECRET') ? (string) constant('PARTY_TURNSTILE_SECRET') : '');

		if ($token === '' || $secret === '') {
			return false;
		}

		$response = Fetch::request(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array('Content-Type: application/json'),
			array('secret' => $secret, 'response' => $token, 'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '')
		);

		$result = json_decode($response, true);

		return is_array($result) && !empty($result['success']);
	}
}
