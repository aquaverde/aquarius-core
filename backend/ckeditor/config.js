/* RTE configuration, copy to /aquarius/ckconfig.js to customise */
CKEDITOR.editorConfig = function( config ) {
    config.filebrowserWindowWidth = 500,
    config.filebrowserWindowHeight = 600,
    config.toolbar = [
       ['Bold','Italic','BulletedList','iLink','Link','Unlink','Image','PasteText','PasteFromWord','RemoveFormat','Maximize','Source']
    ],
    config.toolbarCanCollapse = false,
    config.browserContextMenuOnCtrl = true,
    config.disableObjectResizing = false,
    config.disableNativeTableHandles = false,
    config.resize_enabled = false,
    config.entities = false,
    config.forcePasteAsPlainText = true ,
    config.emailProtection = 'encode',
    config.linkShowAdvancedTab = false,
    config.wordcount = {
        showWordCount: true,
        showCharCount: true,
        countHTML: false
    }

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';

    config.skin = '../../../../backend/ckeditor/skins/bootstrapck';

    config.customConfig = '/aquarius/ckconfig.js';
};

