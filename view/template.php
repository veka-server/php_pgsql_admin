<!DOCTYPE html>
<html lang = "en-US">
<head>
    <meta charset = "UTF-8">
    <title><?php echo $data['title'] ?? ''; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="/asset/css/style.css"  type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <script type="application/javascript" src="/asset/js/jquery.js"></script>
</head>
<body>

    <div id="main_header">
        <div id="logo"><i class="fas fa-database"></i>PGSQL ADMIN</div>
        <ul>
            <li><a href="/" <?php echo OneFileFramework::menu('server') ;?> ><i class="fas fa-server"></i></i>Serveur</a></li>
            <li><a href="/" <?php echo OneFileFramework::menu('database') ;?> ><i class="fas fa-table"></i>Données</a></li>
            <li><a href="/"  <?php echo OneFileFramework::menu('historique') ;?> ><i class="fas fa-history"></i>Historique des requètes</a></li>
            <li><a href="/requestlist" <?php echo OneFileFramework::menu('requestlist') ;?> ><i class="fas fa-stopwatch"></i></i>Requètes en cours</a></li>
            <li><a href="/" <?php echo OneFileFramework::menu('sql') ;?> ><i class="fas fa-cog"></i>SQL</a></li>
            <li><a href="/" <?php echo OneFileFramework::menu('user') ;?> ><i class="fas fa-users"></i></i>Utilisateurs</a></li>
        </ul>
    </div>

    <?php if( isset($data['databases']) ){ ?>
    <div id="left_menu">

        <div class="title">BASE DE DONNÉES</div>
        <div class="elem">
            <select name="database">
                <?php foreach($data['databases'] as $db) : ?>
                <option <?php echo ( isset($_SESSION['current_BDD']) && $_SESSION['current_BDD'] == $db['datname']) ? 'selected="selected"' : '' ; ?> value="<?php echo $db['datname'] ;?>"><?php echo strtoupper($db['datname']) ;?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="title">SCHÉMAS</div>
        <div class="elem">
            <select name="schema"></select>
        </div>

        <div class="title">TABLES</div>
        <div class="elem">
            <select name="table"></select>
        </div>

        <div class="title">INFORMATIONS</div>
        <div class="elem" id="table_list_content">
            <ul class="list_table"></ul>
        </div>

    </div><?php } ?><div id="content_body"><?php echo $data['content'] ?? ''; ?></div>

</body>
</html>