<?php
    const ERROR_REQUIRED = "Veuillez renseigner une todo.";
    const ERROR_TOO_SHORT = "Veuillez entrer au moins 5 caractères.";
    const ERROR_TOO_LONG = "Veuillez entrer au maximum 25 caracteres.";
    const ERROR_EXIST = "Attention! cette tâche existe déja.";
    $filename= __DIR__."/data/todos.json";
    $error = '';
    $todo= '';
    $todos = [];

    if (file_exists($filename)){
        $data = file_get_contents($filename);
        $todos = json_decode($data , true) ?? [];
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $_POST = filter_input_array(INPUT_POST,[
                "todo"=> [
                    'filter'=> FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                    'flags'=> FILTER_FLAG_NO_ENCODE_QUOTES|FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_BACKTICK
                ]
                ]);
        $todo = $_POST['todo'] ?? '';

        if(!$todo){
            $error = ERROR_REQUIRED;
        }else if (mb_strlen($todo) < 5){
            $error = ERROR_TOO_SHORT;
        }else if (mb_strlen($todo) > 25){
            $error = ERROR_TOO_LONG;
        }else if (in_array(mb_strtolower($todo) , array_column($todos,'name'),true)){
            $error = ERROR_EXIST;
        }

        if(!$error){
            $todos = [...$todos, [
                'name' => $todo,
                'done'=> false,
                'id' => time()
            ]];
            file_put_contents($filename,json_encode($todos,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            $todo='';
            header('Location: /');
        }
    }
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Todo</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap"
            rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <script async src="public/js/index.js"></script>
</head>
<body>
    <?php require_once  'includes/header.php' ?>
    <div class="content">
        <div class="todo-container">
            <h1>My todo</h1>
            <form action="/" class="todo-form" method="post">
                <input name="todo" value="<?= $todo ?>" type="text">
                <button class="btn btn-primary">Ajouter</button>
            </form>
            <?php if($error) : ?>
                <p class="text-danger"><?= $error ?></p>
            <?php endif; ?>
            <ul class="todo-list">
                <?php foreach ($todos as $t) : ?>
                    <li class="todo-item <?= $t['done'] ? 'low-opacity' : '' ?>">
                        <span class="todo-name"><?= $t["name"] ?></span>
                        <a href="/edit-todo.php?id=<?= $t['id'] ?>">
                            <button class="btn btn-primary"><?= $t['done'] ? 'Annuler' : 'Valider' ?></button>
                        </a>
                        <a href="/remove-todo.php?id=<?= $t['id'] ?>">
                            <button class="btn btn-small">Supprimer</button>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php require_once 'includes/footer.php' ?>
</body>
</html>