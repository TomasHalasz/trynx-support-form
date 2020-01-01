# Quickstart
This extension add feedback form to your application.

## Instalation
#### Download
The best way to install `Halasz/TrynxSupportForm` is using Composer:
```sh
$ composer require halasz/trynx-support-form
```
#### Registering
You can enable the extension using your neon config:
```sh
extensions:
	TrynxSupportForm: Halasz\Support\SupportExtension
```
#### Injecting
You can simply inject factory in Your Presenters/Services:
```php
public function __construct(Halasz\Support\Support\ISupportFormFactory $SupportFormFactory)
{
    parent::__construct();
    ....
}
```
#### Presenter
When you need to create component in your presenter for use in template you can do it as shown bellow. Don't forget use namespace Halasz:
```php
    protected function createComponentSupportForm()
    {
        return $this->supportForm->create();
    }
	
```
#### Templates
Create method of interface ISupportFormFactory returns an component, so in your .latte you can simply call:
```php
{control SupportForm}
```
Default .latte file used to draw the component you can find in samples folder. You can copy this template and customize it. Path to the template you can specify via your config file.
#### Reqirements
**IMPORTANT!!!** you must link the css and js files from samples folder into your template. 
After that you need to call `halasz.SupportForm.init();` in your js code - **AFTER jQuery!!!**.
This extension requires *jQuery v3.4.1*, *Bootstrap v3.3.7*, *netteForms.js v3* and *html2canvas 1.0.0-rc.5*. All of theese are included in samples folder.

## Configuration
All config is **optional**.

Configuration must be specified in config file:
```sh
TrynxSupportForm:
	template: 'path/to/customized/template.latte'
	maxFiles: 3                                             # max files in multiselect 
	maxFileSize: 3145728                                    # max size of one file in musltiselect (in bytes)
	title: 'Feedback form'                                  # title of modal (top right text)
	invokeButtonText: 'online support'                      # text in button (bottom right on page)
	sendButtonText: 'Send feedback'                         # text in send button
	screenshotButtonText: 'Add Screenshot'                  # text in button which is used to make screenshot
	postUrl: 'http://halasz.ajaximple.cz/www/test/test'     # URL address where may be send data from formular
	syncToken: 'nothing'                                    # sync token, which is send to url with data from formular
	idEmail: 'email_identity_column_name'                   # email column name in $this->user->getIdentity();
	idName: 'username_identity_column_name'                 # username column name in $this->user->getIdentity();    
	flashMessage:
		success: 'Your feedback has been sent. We will send E-Mail to you as soon as possible.'
		error: 'We have encountered an error. Please try it again later.'
	labels:                                                 # labels to form inputs
		message: 'Your message'
		image: 'Add screenshot'
		files: 'Add files'
		name: 'Name'
		email: 'E-Mail'
		subject: 'Subject'
	errors:                                                 # Theese texts are shown under inputs when there is an error.
		message: 'Required'
		name: 'Required'
		email: 'Required'
		subject: 'Required'
		image: 'Uploaded file must be an image'
		files_max: 'You can upload maximum %d files'
		files_size: 'Maximum file size is 3MB'
		files_mimes: 'Is allowed to upload only files with this types: GIF, JPG, PNG, txt, xls, doc, zip or rar'
```
## Receive data on specified URL
#### In PHP:
**Data are sent by POST method.**
In $_POST are included data in format:
##### If user is logged in:
```php
[
	'sync_token' => 'token',
	'dataxml' => '<xml>
<cl_users_id>123</cl_users_id>
<email>email</email>
<user_name>name</user_name>
<subject>subject</subject>
<message>message</message>
<screenshot>screenshot in BASE64</screenshot>
<file1>fileName1.ext</file1>
<file2>fileName2.ext</file2>
<file3>fileName3.ext</file3>
</xml>'
]
```
##### If user is not logged in:
```php
[
	'sync_token' => 'tokem',
	'dataxml' => '<xml>
<email>email</email>
<user_name>name</user_name>
<subject>subject</subject>
<message>message</message>
<screenshot>screenshot in BASE64</screenshot>
<file1>fileName1.ext</file1>
<file2>fileName2.ext</file2>
<file3>fileName3.ext</file3>
</xml>'
]
```
in both cases can be files1-3 accessed via $_FILES
Example of $_FILES content 
```php
[
	'fileName1_ext' => [
		name => 'randomTMPname',
		type => 'mime/type',
		tmp_name => 'path/to/tmp/folder/and/tmp/name/of/file'
		error => value,
		size => sizeOfFileInBytes
	],
	'fileName2_ext' => [...],
	'fileName3_ext' => [...]
]
```
### Screenshot tips
Screenshot is generated from body element of page. Screenshot is taken according to size of body element. In some layout cases is possible that body doesn't have correct height, so if this issue hapen's to you, check height of body element. 


## Returned data from specified URL
This component is expecting XML with specified structure.
```xml
<xml>
<status></status>
<error_message></error_message>
</xml>
```
##### If everything is correctly processed:
```xml
<xml>
<status>OK</status>
<error_message></error_message>
</xml>
```
##### In case of some errors:
```xml
<xml>
<status>ERROR</status>
<error_message>Described error. Remember, it's shown to user.</error_message>
</xml>
```




## Translation
Component has autowired translator, which is registered in config file and implements `Nette\Localization\ITranslator` see [Nette Localization](https://doc.nette.org/cs/3.0/localization "Nette Localization")
## Conclusion
This extension requires Nette3 and it is property of Tomas Halász © 2019