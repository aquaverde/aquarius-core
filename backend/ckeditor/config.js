CKEDITOR.editorConfig = function( config ) {
    config.skin = 'aquaskin,skins/aquaskin/' ;
    config.extraPlugins = 'iLink,charcounter';

    config.toolbar = [
        ['Styles','Bold','Italic','-','BulletedList','-','iLink','Link','Unlink','Image','-','PasteText','PasteFromWord','RemoveFormat','-','Maximize','Source']
    ] ;

    config.toolbarCanCollapse = false;
    config.browserContextMenuOnCtrl = true ;
    config.disableObjectResizing = false ;
    config.disableNativeTableHandles = false ;
    config.resize_enabled = false;
    config.entities = false;
    config.forcePasteAsPlainText = true ;
    config.emailProtection = 'encode';
    config.linkShowAdvancedTab = false;

    // Disable the useless filler text
    config.image_previewText = ' ';
    
    // Load site-specific config if available
    config.customConfig = 'config.js.php';

    // Disable loading of styles plugin
    config.stylesSet = '';

};



