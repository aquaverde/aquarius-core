/* Customization directives for the CKEditor
 * This file is interpreted by PHP, so you can add PHP directives if necessary.
 */

CKEDITOR.editorConfig = function( config ) {
    config.skin = 'aquaskin,skins/aquaskin/' ;
    config.extraPlugins = 'iLink,charcounter';

    /* minimal */
    config.toolbar = [
        ['Bold','Italic','-','BulletedList','-','iLink','Link','Unlink','-','PasteText','PasteFromWord','RemoveFormat','-','Maximize','Source']
    ] ;

    /* standard
    config.toolbar = [
        ['Bold','Italic','-','BulletedList','-','iLink','Link','Unlink','-','PasteText','PasteFromWord','RemoveFormat','-','Maximize','Source']
    ] ;
    */

    config.toolbarCanCollapse = false;
    config.browserContextMenuOnCtrl = true ;
    config.disableObjectResizing = false ;
    config.disableNativeTableHandles = false ;
    config.resize_enabled = false;
    config.entities = false;
    config.forcePasteAsPlainText = true ;
    config.emailProtection = 'encode';
    config.linkShowAdvancedTab = false;

};

/*
 
 CKEDITOR.stylesSet.add('default', [
    {name:'Link',element:'a',attributes:{'class':'teaserLink'}},
    {name:'Auszeichnung Immobilien',element:'span',attributes:{'class':'immo'}},
    {name:'Auszeichnung Verwaltung',element:'span',attributes:{'class':'verw'}}
]);
 
 */

/*
CKEDITOR.stylesSet.add('default', [
    {name:'Blue Title',element:'h3',styles:{color:'Blue'}},
    {name:'Red Title',element:'h3',styles:{color:'Red'}},
    {name:'Marker: Yellow',element:'span',styles:{'background-color':'Yellow'}},
    {name:'Marker: Green',element:'span',styles:{'background-color':'Lime'}},
    {name:'Big',element:'big'},
    {name:'Small',element:'small'},
    {name:'Typewriter',element:'tt'},
    {name:'Computer Code',element:'code'},
    {name:'Keyboard Phrase',element:'kbd'},
    {name:'Sample Text',element:'samp'},
    {name:'Variable',element:'var'},
    {name:'Deleted Text',element:'del'},
    {name:'Inserted Text',element:'ins'},
    {name:'Cited Work',element:'cite'},
    {name:'Inline Quotation',element:'q'},
    {name:'Language: RTL',element:'span',attributes:{dir:'rtl'}},
    {name:'Language: LTR',element:'span',attributes:{dir:'ltr'}},
    {name:'Image on Left',element:'img',attributes:{style:'padding: 5px; margin-right: 5px',border:'2',align:'left'}},
    {name:'Image on Right',element:'img',attributes:{style:'padding: 5px; margin-left: 5px',border:'2',align:'right'}},
    {name:'Borderless Table',element:'table',styles:{'border-style':'hidden','background-color':'#E6E6FA'}},
    {name:'Square Bulleted List',element:'ul',styles:{'list-style-type':'square'}}
]);
*/

/*

config.toolbar_Full =
[
    ['Source','-','Save','NewPage','Preview','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
    '/',
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Anchor'],
    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    '/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor'],
    ['Maximize', 'ShowBlocks','-','About']
];

*/