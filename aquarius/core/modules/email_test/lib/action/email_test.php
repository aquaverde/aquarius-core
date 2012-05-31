<?php

class action_email_test extends AdminAction {
    function get_title() {
        return new FixedTranslation("Send test emails");
    }
    
    function permit_user($user) {
        return $user->isSuperadmin();
    }
}

class action_email_test_form extends action_email_test  implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('actions', array(Action::make('email_test', 'send'), Action::make('cancel')));
        $result->use_template('email_test_form.tpl');
    }
}

class action_email_test_send extends action_email_test  implements ChangeAction {
    
    function php_mail($from, $to, $subject, $body) {
        return mail($to, $subject, $body, "From: $from");
    }

    function swift_html($from, $to, $subject, $body) {
        require_once 'lib/swift/swift_required.php';
        $message = Swift_Message::newInstance();
        $message->setFrom($from);
        $message->setTo($to);
        $message->setSubject($subject);
        $message->setBody($body);

        $mailer = Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
        return $mailer->send($message);
    }
    
    function process($aquarius, $post, $result) {
        $to   = get($post, 'to');
        $from = get($post, 'from');
        if (strlen($from) < 1) $from = $to;
        $subject = "";
        $body = <<<MARKOV4_FTW
aquaverde, agence web individuelle à ces questions primordiales.
conception
Le point fort du tourisme et modules développés en seront mis en page automatiquement.

aquaverde, agence web à Internet. Toutes les besoins des rubriques, en passant par le site web à votre site. Pas d'installation de connaissances préalables: aquarius fonctionne avec un navigateur connecté à la création de remplir les domaines de vos clients des masques de vos besoins, ceux de saisies personnalisés en sont réalisées de contenus d'aquaverde "aquarius" vous permet de remplir les moteurs de votre site ? Quels en sont ses clients ? Quels objectifs marketing désirez-vous atteindre grâce au site web à Bienne
Spécialisée depuis 2001 dans les champs, les tâches sont réalisées de textes et du voyage est mise à votre site. Pas d'installation de votre disposition. Conseils, conception, création, design, webdesign, programmation, CMS, optimisation pour les tâches sont vos besoins, ceux de gérer votre disposition. Conseils, conception, création, design, webdesign, programmation, CMS, optimisation pour les coûts ? Faut-il changer d'hébergeur, comment cela ce passe t-il ? Quels en sont réalisées de fichiers PDF vous aidons volontiers à votre site. Pas d'installation de pages. Il suffit de programmes spéciaux, pas de recherche (SEO) font partie intégrante de proposer des rubriques, en passant par le site web dépend d'un concept réfléchi. Après l'analyse de sites web fonctionnels, esthétiques et efficaces, l'agence web à Bienne
Spécialisée depuis 2001 dans les besoins des rubriques, en sont ses buts ? Quels sont ses clients sont réalisées de remplir les tâches sont réalisées de connaissances préalables: aquarius fonctionne avec un navigateur connecté à la gestion des masques de proposer des utilisateurs cibles, aquaverde de votre site. Pas d'installation de vos objectifs et modules développés en passant par le contrôle de vos clients sont vos besoins, ceux de programmes spéciaux, pas de remplir les champs, les moteurs de vos objectifs et du tourisme et simple
De l'intégration et les champs, les domaines de bienne propose à votre entreprise.
content management system aquarius
Le point fort du tourisme et la création de connaissances préalables: aquarius est de connaissances préalables: aquarius fonctionne avec nos compétences.
conseils
Quels sont réalisées de l'entreprise, du tourisme et la création de l'entreprise, du tourisme et modules développés en fonction des différents types de connaissances préalables: aquarius fonctionne avec un navigateur connecté à ses clients ? Quels sont disponibles.
intuitif et modules développés en collaboration avec un navigateur connecté à ses clients sont réalisées de connaissances préalables: aquarius fonctionne avec nos compétences.
conseils
Quels objectifs marketing désirez-vous atteindre grâce au site web fonctionnels, esthétiques et la création de fichiers PDF vous aidons volontiers à ses buts ? Qui utilisera le webdesign et images, à répondre à votre site web aquaverde développe une solution web à votre entreprise.
content management system aquarius
Le point fort du voyage est mise à répondre à répondre à ses clients ? Qui utilisera le site web à votre entreprise.
content management system aquarius
Le gestionnaire de bienne propose à distance.
MARKOV4_FTW;
        
        $ok = 0;
        $ok_message = AdminMessage::with_html('ok', "Sending test mails to $to");
        $fail = 0;
        $fail_message = AdminMessage::with_html('warn', "Sending test mails to $to");
        foreach(array('php_mail', 'swift_html') as $method) {
            $success = $this->$method($from, $to, $method.': '.$subject, $body);
            if ($success) {
                $ok_message->add_html("Method $method reported success");
                $ok += 1;
            } else {
                $fail_message->add_html("Method $method reported failure");
                $fail += 1;
            }
        }
        if ($ok > 0)   $result->add_message($ok_message);
        if ($fail > 0) $result->add_message($fail_message);
    }
}