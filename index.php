<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//ajout de commentaire spécial
/* 
    contrôleur de la page d'accueil
*/
function getHome()
{
    $flashMessage = getFlashMessage();
    $articleModel = new ArticleModels();
    $articles = $articleModel->getAllArticles(5);
    //Affichage inclusion du fichier template
    $title = "Accueil";
    $template = 'home';
    include TEMPLATE_DIR . '/base.phtml';
}

function getArticle()
{
    $articleModel = new ArticleModels();
    $commentModel = new CommentModels();
    if(!array_key_exists('idArticle', $_GET) || !ctype_digit($_GET['idArticle']) ){
        echo '<?p> Id manquant ou incorrect </?p>';
        exit;
    }
    $idarticle = $_GET['idArticle'];

    $articles = $articleModel->getOneArticle($idarticle);

    //test pour savoir si l'article existe
    if(!$articles){
        echo 'ERREUR : aucun article possédant cet article';
        exit;
    }
    //traitement des données du formulaire d'ajout de commentaires

    if(!empty($_POST)){
        //récupération des données
        $content = trim($_POST['content']);
        $rate = (int) ($_POST['rate']);
        
        //@TODO validation 
        $errors = [];
        //si le champ content est vide = message d'erreur
        if(!$content){
            $errors['content'] = 'le champ "Commentaire" est obligatoire';
        }

        if (empty($errors)){
            $commentModel->getArticleComment($content,$idarticle,$rate,$_SESSION['user']['id']);
            header('location: index.php?action=article&idArticle=' . $idarticle);
            exit ;
        }
        //Redirection 
        
         
    }
    $comments = $commentModel->showArticleComment($idarticle);
    
    $template = 'article';
    include TEMPLATE_DIR . '/base.phtml';
}


function getContact()
{
    
    $title = "contact";
    $template = 'contact';
    include TEMPLATE_DIR . '/base.phtml';
}
function getMention()
{
    $template = 'mentions';
    include TEMPLATE_DIR . '/mentions.phtml';
}
function getSignup()
{
    $userModel = new UserModels();
    $lastName = '';
    $firstName = '';
    $email = '';
    //si le formulaire est soumis
    if(!empty($_POST)){
        // Récupération des données
        $firstName = trim($_POST['firstName']);
        $lastName = trim($_POST['lastName']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $passwordConfirm = $_POST['password-confirm'];
        $errors = [];
        //Gestion des possibles erreurs du formulaire
        if (!$firstName) { 
            $errors['firstName'] = 'Le champ "Prénom" est obligatoire';
        }
        if (!$lastName) { 
            $errors['lastName'] = 'Le champ "Nom" est obligatoire';
        }
        if (!$email) { 
            $errors['email'] = 'Le champ "Email" est obligatoire';
        }

        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
            $errors['email'] = "Le format de l'email n'est pas correct";
        }
        elseif($userModel->emailExist($email)){
            $errors['emailExist'] = "l'email existe déjà";
        } 
        if (!$password) { 
            $errors['password'] = 'Le champ "Mot de passe" est obligatoire';
        }
        elseif (strlen($password) < 8) {
            $errors['password'] = 'Le champ "Mot de passe" doit comporter au moins 8 caractères';
        }
    
        elseif($password != $passwordConfirm) {
            $errors['password-confirm'] = 'Les mots de passe ne sont pas identiques';
        }

        if(!$errors) {
            $hash = password_hash($password,PASSWORD_DEFAULT);
            $userModel->addNewUser($firstName,$lastName,$email,$hash);     
            addFlashMessage('votre compte a bien été crée');
            header('Location: index.php?action=home');
            exit;
        }
    }
    $template = 'signup';
    include TEMPLATE_DIR . '/base.phtml';
}
function getLogin()
{
    $userModel = new UserModels();
    $email = '';
    if(!empty($_POST)){
        $email = trim($_POST['email']);
        $password = $_POST['password']; 
        $user = $userModel->checkCredentials($email, $password);

        if($user){
            userRegister($email, $user['idUser'], $user['lastName'], $user['role']);
            addFlashMessage(('bonjour' . ' ' . $user['lastName']));
            if(isAdmin()){
                
                header('Location: index.php?action=admin');
                exit;
            }
            header('Location: index.php?action=home');
            exit;
        }
        $errors['message'] = 'Identifiants inccorects';
    }
    
    $template = 'login';
    include TEMPLATE_DIR . '/base.phtml';
}

function getLogout()
{
    logout();
    header('Location: index.php?action=home');
    exit;
    $template = 'logout';
    include TEMPLATE_DIR . '/logout.phtml';
}