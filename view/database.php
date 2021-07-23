<div id="bloc_request">
    <textarea name="bloc_request" id="textarea_request"></textarea>
    <br/>
    <input type="button" value="Executer" name="executer_request" id="btn_execute_request"/>
    <input type="button" value="Exporter en CSV" name="export_csv" id="export_csv"/>
    <input type="button" value="Exporter en SQL" name="export_sql" id="export_sql"/>
    <div id="stat_request"></div>
</div>

<div id="show_data"></div>

<script>

    var add_loading_schema = function(){
        var schema_select = $('select[name="schema"]');
        schema_select.find('option').remove();
        schema_select.attr('disabled', 'disabled');
        schema_select.append('<option>... chargement ...</option>');
    }

    var add_loading_table = function(){
        var table_select = $('select[name="table"]');
        table_select.find('option').remove();
        table_select.attr('disabled', 'disabled');
        table_select.append('<option>... chargement ...</option>');

        var textarea = $('#textarea_request');
        textarea.attr('disabled', 'disabled');
        $('#show_data').html('');
    }

    $('select[name="database"]').change(function(){
        add_loading_schema();
        add_loading_table();
        $.post( "/getschema",{database:$(this).val()}, function( data ) {
            var schema_select = $('select[name="schema"]');
            schema_select.find('option').remove();
            $.each(data.schemas, function(key, value){
                schema_select.append('<option value="'+value.table_schema+'">'+value.table_schema.toUpperCase()+'</option>');
            });

            if(data.current_schema != null  && data.current_schema != ''){
                schema_select.val(data.current_schema);
            }

            schema_select.change();
            schema_select.removeAttr('disabled');
        }, 'json');
    });

    $('select[name="schema"]').change(function(){
        add_loading_table();
        $.post( "/gettable",{schema:$(this).val()}, function( data ) {
            var table_select = $('select[name="table"]');
            table_select.find('option').remove();
            $.each(data.tables, function(key, value){
                table_select.append('<option value="'+value.table_name+'">'+value.table_name.toUpperCase()+'</option>');
            });

            if(data.current_table != null && data.current_table != ''){
                table_select.val(data.current_table);
            }

            table_select.removeAttr('disabled');
            table_select.change();
        }, 'json');
    });

    $('select[name="table"]').change(function(){
        var table_select = $('select[name="table"]');
        table_select.attr('disabled', 'disabled');

        var sql = 'SELECT * FROM '+$('select[name="schema"]').val()+'.'+ table_select.val();

        /** @todo requete pour recup le nombre aproximatif de ligne dans la table
/*
        preg_match_all("/\bselect\b/i",$clean_sql,$match_select);
        preg_match_all("/\bfrom\b/i",$clean_sql,$match_from);
        preg_match_all("/\bjoin\b/i",$clean_sql,$match_join);

        if(
            count($match_select[0] ?? []) == 1 // un seul SELECT
            AND count($match_from[0] ?? []) == 1 // un seul FROM
        AND count($match_join[0] ?? []) == 0 // pas de jointure
        AND $last_page_requested == FALSE
    ){
             // estimation du nombre de ligne d'une table a partie des données imprécisent de PGSQL
             // mais beaucoup plus rapide rapide
            $sub_request = "SELECT reltuples::bigint AS estimate FROM pg_class WHERE oid = 'projection_compta_campagne'::regclass";
//            $clean_sql = preg_replace('/SELECT/i','SELECT ('.$sub_request.') AS count_self , ',  $clean_sql, 1);
        }
*/

        $('#textarea_request').val(sql);

        $('#btn_execute_request').click();
        table_select.removeAttr('disabled', 'disabled');
    });

    var execute_request = function(page_curr){

        var textarea = $('#textarea_request');
        textarea.attr('disabled', 'disabled');
        $('#btn_execute_request').attr('disabled', 'disabled');
        $.post( "/request",{request:textarea.val(), table:$('select[name="table"]').val(), page_curr : page_curr}, function( data ) {

            var html = 'Aucune données.';
            if(data.datas.length > 0){

                var last_page_class = '';
                var max_page = '';
                if(data.infos.is_last_page == true) {
                    last_page_class = 'is_last_page';
                    max_page = ' max="'+data.infos.page_curr+'"';
                }

                var html = '<table class="blueTable" id="requlist">';
                html += '<thead><tr>';
                for( var j in data.datas[0] ) {
                    html += '<th>' + j + '</th>';
                }
                html += '</tr></thead><tbody>';
                for( var i = 0; i < data.datas.length; i++) {
                    html += '<tr>';
                    for( var j in data.datas[i] ) {
                        html += '<td>' + data.datas[i][j] + '</td>';
                    }
                    html += '</tr>';
                }
                html += '</tbody></table>';

                html += '<div class="pagination"> ';
                html += '<input type="button" value="Début" class="first_page" data-curr-page="'+data.infos.page_curr+'"/> ';
                html += '<input type="button" value="Precedent" class="prev_page" data-curr-page="'+data.infos.page_curr+'"/> ';
                html += ' Page <input type="number" min="1" '+max_page+' value="'+data.infos.page_curr+'" id="page_direct" />';
                html += '<input type="button" value="Suivant" class="next_page '+last_page_class+'" data-curr-page="'+data.infos.page_curr+'"/> ';
                html += '<input type="button" value="Fin" class="last_page '+last_page_class+'" /> ';
                html += '</div> ';
            }

            $('#show_data').html(html);

            $('.first_page').click(function(){
                var curr_page = parseInt($(this).attr('data-curr-page'));
                if(curr_page === 1){
                    return ;
                }
                execute_request(0);
            });

            $('.prev_page').click(function(){
                var curr_page = parseInt($(this).attr('data-curr-page'));
                if(curr_page === 1){
                    return ;
                }
                execute_request(curr_page - 1);
            });

            $('.next_page').click(function(){
                if($(this).hasClass('is_last_page')){
                    return ;
                }
                execute_request(parseInt($(this).attr('data-curr-page'))+1);
            });

            $('.last_page').click(function(){
                if($(this).hasClass('is_last_page')){
                    return ;
                }
                execute_request('last');
            });

            $('#page_direct').change(function(){
                execute_request($(this).val());
            });

            function pad (str, max) {
                str = str.toString();
                return str.length < max ? pad("0" + str, max) : str;
            }

            var millisToMinutesAndSeconds = function(millis) {
                millis = (millis*1000).toFixed(0);
                var minutes = Math.floor(millis / 60000);
                var seconds = Math.floor((millis % 60000) / 1000);
                millis -= (minutes * 60000) + (seconds * 1000);
                var array = [];
                if(minutes > 0) { array.push(minutes+' minutes '); }
                if(seconds > 0) { array.push(pad(seconds,2) + ' secondes '); }
                if(millis > 0) { array.push(millis.toFixed(0) + ' millisecondes ' ); }
                return array.join(' ');
            }

            var stat = 'Temps d\'exécution : '+millisToMinutesAndSeconds(data.infos.execution_time);

            $('#stat_request').html(stat);

            $('#btn_execute_request').removeAttr('disabled');
            textarea.removeAttr('disabled');
        }, 'json');
    }

    $('#btn_execute_request').click(function(){
        execute_request(0);
    });

    $('select[name="database"]').change();

</script>