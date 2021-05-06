<?php

namespace Halasz\Support\Support;

use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

use CURLFile;

class RenderedFormFactory
{
    use \Nette\SmartObject;

    /**
     * @var User
     */
    private $user;

    const
        FORM_INPUT_MESSAGE = 'message',
        FORM_INPUT_IMAGE = 'image',
        FORM_INPUT_FILES = 'files',
        FORM_INPUT_NAME = 'name',
        FORM_INPUT_EMAIL = 'email',
        FORM_INPUT_SUBJECT = 'subject';

    /**
     * @var array
     */
    private $labels;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var int
     */
    private $maxFiles;

    /**
     * @var int
     */
    private $maxFileSize;

    /**
     * @var string
     */
    private $postUrl;

    /**
     * @var string
     */
    private $idEmail;

    /**
     * @var string
     */
    private $idEmail2;

    /**
     * @var string
     */
    private $defaultEmail;

    /**
     * @var string
     */
    private $idName;

    /**
     * @var string
     */
    private $syncToken;

    /**
     * @var string
     */
    private $sendButtonText;

    /**
     * @var bool
     */
    public static $hasError = false;

    /**
     * @var callable
     */
    public $setErrorMessage;

    /**
     * @var array
     */
    private $mimeTypes = [
        'image/*',                                                                  // Images
        'application/zip',                                                          // ZIP
        'application/x-rar-compressed',                                             // RAR
        'application/msword',                                                       // doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // docx
        'application/vnd.ms-excel',                                                 // xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',        // xlsx
        'text/plain'                                                                // txt
    ];

    public function __construct(
        User $user)
    {
        $this->user = $user;
    }

    public function create(ITranslator $translator = null, bool $turnOffAutocomplete = false, $setErrorMessage): Form
    {
        $form = new Form();
        $this->setErrorMessage = $setErrorMessage;
        $form->setTranslator($translator);
        $formPrototype = $form->getElementPrototype();
        $formPrototype->setAttribute('novalidate', 'novalidate');

        if ($turnOffAutocomplete) {
            $formPrototype->setAttribute('autocomplete', 'off');
        }

        if (!$this->user->isLoggedIn()) {
            $form->addText(self::FORM_INPUT_NAME, $this->labels[self::FORM_INPUT_NAME])
                ->setRequired($this->errors[self::FORM_INPUT_NAME]);
            $form->addEmail(self::FORM_INPUT_EMAIL, $this->labels[self::FORM_INPUT_EMAIL])
                ->setRequired($this->errors[self::FORM_INPUT_EMAIL]);
        }

        $form->addTextArea(self::FORM_INPUT_MESSAGE, $this->labels[self::FORM_INPUT_MESSAGE], 5, 10)
            ->setRequired($this->errors[self::FORM_INPUT_MESSAGE]);
        $form->addText(self::FORM_INPUT_SUBJECT, $this->labels[self::FORM_INPUT_SUBJECT])
            ->setRequired($this->errors[self::FORM_INPUT_SUBJECT]);
        $form->addText(self::FORM_INPUT_IMAGE, $this->labels[self::FORM_INPUT_IMAGE])
            ->setHtmlId('halasz_feedback_form_' . self::FORM_INPUT_IMAGE);
        $form->addMultiUpload(self::FORM_INPUT_FILES, $this->labels[self::FORM_INPUT_FILES])
            ->setRequired(false)
            ->setHtmlId('halasz_feedback_form_' . self::FORM_INPUT_FILES)
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, $this->errors[self::FORM_INPUT_FILES . '_max'], $this->maxFiles)
            ->addRule(Form::MAX_FILE_SIZE, $this->errors[self::FORM_INPUT_FILES . '_size'], $this->maxFileSize)
            ->addRule(Form::MIME_TYPE, $this->errors[self::FORM_INPUT_FILES . '_mimes'], $this->mimeTypes);

        $form->onSuccess[] = [$this, 'onSuccess'];

        $form->addSubmit('submit', $this->sendButtonText);

        return $form;
    }

    public function onSuccess(Form $form, ArrayHash $values): void
    {
        $post = [
            'sync_token' => $this->syncToken
        ];

        $post = $this->generateXML($post, $values);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->postUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($server_output);
        if ($xml->status != 'OK') {
            Debugger::log('halaszFeedbackFormLOG ' . $server_output);
            self::$hasError = true;
            call_user_func($this->setErrorMessage,(string)$xml->error_message);
        }else{
            //  Debugger::log('halaszFeedbackFormLOG success ' . $post);
            $form->reset();
        }
    }


    private function generateXML(array $post, ArrayHash $values): array
    {
        $userIdentity = $this->user->getIdentity();

        $post['dataxml'] = '<xml>';
        if ($this->user->isLoggedIn()) {
            $post['dataxml'] .= '<cl_users_id>' . $this->user->id . '</cl_users_id>';
            (!empty($userIdentity->{$this->idEmail})) ? $tmpEmail = $userIdentity->{$this->idEmail} : $tmpEmail = "";
            if (!empty($userIdentity->{$this->idEmail2}))
            {
                $tmpEmail = $userIdentity->{$this->idEmail2};
            }else{
                $tmpEmail = $this->defaultEmail;
            }
            $post['dataxml'] .= '<email>' . $tmpEmail . '</email>';
            $post['dataxml'] .= '<user_name>' . $userIdentity->{$this->idName} . '</user_name>';
        } else {
            $post['dataxml'] .= '<email>' . $values->{self::FORM_INPUT_EMAIL} . '</email>';
            $post['dataxml'] .= '<user_name>' . $values->{self::FORM_INPUT_NAME} . '</user_name>';
        }

        $post['dataxml'] .= '<subject>' . $values->{self::FORM_INPUT_SUBJECT} . '</subject>';
        $post['dataxml'] .= '<message>' . $values->{self::FORM_INPUT_MESSAGE} . '</message>';
        $post['dataxml'] .= '<screenshot>' . $values->{self::FORM_INPUT_IMAGE} . '</screenshot>';

        $post = $this->addFilesToPOST($post, $values);

        $post['dataxml'] .= '</xml>';

        return $post;
    }

    private function addFilesToPOST(array $post, ArrayHash $values): array
    {
        for ($i = 0; $i < $this->maxFiles; $i++) {
            if (isset($values->{self::FORM_INPUT_FILES}[$i])) {
                $post['dataxml'] .= '<file' . ($i + 1) . '>';
                $fileUpload = $values->{self::FORM_INPUT_FILES}[$i];
                $name = $fileUpload->getName();
                $post['dataxml'] .= $name;
                $post[$name] = new CURLFile($fileUpload->getTemporaryFile(), $fileUpload->getContentType());
                $post['dataxml'] .= '</file' . ($i + 1) . '>';
            }
        }

        return $post;
    }

    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    public function setPostUrl(string $url): void
    {
        $this->postUrl = $url;
    }

    public function setIdEmail(string $idEmail): void
    {
        $this->idEmail = $idEmail;
    }

    public function setIdEmail2(string $idEmail): void
    {
        $this->idEmail2 = $idEmail;
    }

    public function setDefaultEmail(string $defaultEmail): void
    {
        $this->defaultEmail = $defaultEmail;
    }

    public function setIdName(string $idName): void
    {
        $this->idName = $idName;
    }

    public function setSyncToken(string $token): void
    {
        $this->syncToken = $token;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    //public function setErrorMessage(string $errorMessage): void
    //{
    //$this->errors = $errors;
    //}


    public function setMaxFiles(int $count): void
    {
        $this->maxFiles = $count;
    }

    public function setMaxFileSize(int $size): void
    {
        $this->maxFileSize = $size;
    }

    public function setSendButtonText(string $text): void
    {
        $this->sendButtonText = $text;
    }

}
