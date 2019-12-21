<?php

namespace Halasz\Support;

use Nette\DI\CompilerExtension;
use Halasz\Support\Support\RenderedFormFactory;

class SupportExtension extends CompilerExtension
{
    private $defaults = [
        'template' => (__DIR__ . '/samples/simpleTemplate.latte'),
        'maxFiles' => 3,
        'maxFileSize' => (3 * 1024 * 1024), 
        'title' => 'Feedback form',
        'invokeButtonText' => 'online support',
        'sendButtonText' => 'Send feedback',
        'screenshotButtonText' => 'Add Screenshot',
        'postUrl' => 'http://halasz.ajaximple.cz/www/test/test',
        'syncToken' => 'nothing',
        'flashMessage' => [
            'success' => 'Your feedback has been sent. We will send E-Mail to you as soon as possible.',
            'error' => 'We have encountered an error. Please try it again later.',
        ],
        'labels' => [
            RenderedFormFactory::FORM_INPUT_MESSAGE => 'Your message',
            RenderedFormFactory::FORM_INPUT_IMAGE => 'Add screenshot',
            RenderedFormFactory::FORM_INPUT_FILES => 'Add files',
            RenderedFormFactory::FORM_INPUT_NAME => 'Name',
            RenderedFormFactory::FORM_INPUT_EMAIL => 'Surname',
            RenderedFormFactory::FORM_INPUT_SUBJECT => 'Subject'
        ],
        'errors' => [
            RenderedFormFactory::FORM_INPUT_MESSAGE => 'Required',
            RenderedFormFactory::FORM_INPUT_NAME => 'Required',
            RenderedFormFactory::FORM_INPUT_EMAIL => 'Required',
            RenderedFormFactory::FORM_INPUT_SUBJECT => 'Required',
            RenderedFormFactory::FORM_INPUT_IMAGE => 'Uploaded file must be an image',
            RenderedFormFactory::FORM_INPUT_FILES . '_max' => 'You can upload maximum %d files',
            RenderedFormFactory::FORM_INPUT_FILES . '_size' => 'Maximum file size is 3MB',
            RenderedFormFactory::FORM_INPUT_FILES . '_mimes' => 'Is allowed to upload only files with this types: GIF, JPG, PNG, txt, xls, doc, zip or rar',
        ]
    ];
    
    public function loadConfiguration()
    {
        $defaults = $this->validateConfig($this->defaults);
        $builder = $this->getContainerBuilder();
        
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__ . '/config/common.neon')['services']
        );
        
        $builder->getDefinition('HalaszRenderedFormFactory')
                ->addSetup('setMaxFiles', [$defaults['maxFiles']])
                ->addSetup('setMaxFileSize', [$defaults['maxFileSize']])
                ->addSetup('setPostUrl', [$defaults['postUrl']])
                ->addSetup('setSyncToken', [$defaults['syncToken']])
                ->addSetup('setSendButtonText', [$defaults['sendButtonText']])
                ->addSetup('setLabels', [$defaults['labels']])
                ->addSetup('setErrors', [$defaults['errors']]);
        
        $builder->getDefinition('HalaszSupportForm')
                ->getResultDefinition()
                ->addSetup('setTitle', [$defaults['title']])
                ->addSetup('setInvokeButtonText', [$defaults['invokeButtonText']])
                ->addSetup('setTemplatePath', [$defaults['template']])
                ->addSetup('setFlashMessage', [$defaults['flashMessage']])
                ->addSetup('setScreenshotButtonText', [$defaults['screenshotButtonText']]);
    }
}
