<?php

namespace Halasz\Support\Support;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\ComponentModel\IComponent;
use Nette\Http\Request;

use Halasz\Support\Support\RenderedFormFactory;

class SupportForm extends Control
{

    /**
     * @var Request
     */
    private $httpRequest;

    /**
     * @var RenderedFormFactory
     */
    private $renderedFormFactory;

    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * @var string
     */
    private $templatePath;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $invokeButtonText;

    /**
     * @var string
     */
    private $screenshotButtonText;

    /**
     * @var string
     */
    private $messages;
	
	/**
	 * @var string
	 */
	private $errorMessage;
	

    public function __construct(
            RenderedFormFactory $renderedFormFactory,
            Request $httpRequest,
            ITranslator $translator = null)
    {
        $this->translator = $translator;
        $this->renderedFormFactory = $renderedFormFactory;
        $this->httpRequest = $httpRequest;
		//$this->renderedFormFactory->setErrorMessage[] = function($message){$this->setErrorMessage($message);};
    }
    
    public function render(): void
    {
        $this->template->title = $this->title;
        $this->template->invokeButtonText = $this->invokeButtonText;
        $this->template->addScreenShot = $this->screenshotButtonText;
        
        $this->template->setTranslator($this->translator);
        $this->template->render($this->templatePath);
    }
    
    public function createComponentRenderedForm(): IComponent
    {
        $form = $this->renderedFormFactory->create($this->translator, false, function($message){$this->setErrorMessage($message);});
        $form->onValidate[] = [$this, 'onValidate'];
        $form->onSuccess[] = [$this, 'onSuccess'];
        return $form;
    }
    
    public function onValidate(): void
    {
        if ($this->httpRequest->isAjax()) {
            $this->redrawControl('halaszFeedbackForm');
        }
    }
    
    public function onSuccess(): void
    {
        if (RenderedFormFactory::$hasError) {
            $this->flashMessage($this->messages['error'], 'halaszFlashError');
            if ($this->errorMessage != '') {
				$this->flashMessage($this->errorMessage, 'halaszFlashError');
			}
        } else {
            $this->flashMessage($this->messages['success'], 'halaszFlashSuccess');
        }
        if (!$this->httpRequest->isAjax()) {
            $this->redirect('this');
		}
    }
    
    public function setErrorMessage($message): void
	{
		$this->errorMessage = $message;
	}
    
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    
    public function setInvokeButtonText(string $text): void
    {
        $this->invokeButtonText = $text;
    }
    
    public function setScreenshotButtonText(string $text): void
    {
        $this->screenshotButtonText = $text;
    }
    
    public function setTemplatePath(string $path): void
    {
        $this->templatePath = $path;
    }
    
    public function setFlashMessage(array $messages): void
    {
        $this->messages = $messages;
    }
    
    public function flashMessage($message, string $type = 'info'): \stdClass
    {
        if ($this->translator) {
            $message = $this->translator->translate($message);
        }
        return parent::flashMessage($message, $type);
    }
}
