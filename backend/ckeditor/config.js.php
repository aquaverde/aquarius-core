<?php
// This file loads the custom per-site configuration of CKeditor
$configf = '../../../ckconfig.js';
if (file_exists($configf)) {
    readfile($configf);
}