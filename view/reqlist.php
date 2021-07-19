<div style="margin:15px;">

    <table class="blueTable" id="requlist">
        <thead>
        <tr>
            <td>PID</td>
            <td>Database</td>
            <td>Query</td>
            <td>Time</td>
            <td>State</td>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data['reqlist'] as $line) : ?>
            <tr class="<?php echo $line['database']; ?>">
                <td><?php echo $line['pid']; ?></td>
                <td><?php echo $line['database']; ?></td>
                <td><?php echo $line['query']; ?></td>
                <td><?php echo $line['time']; ?></td>
                <td><?php echo $line['state']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>

    $('select[name="database"').change(function(){
        $('#requlist tbody tr').show();
        $('#requlist tbody tr:not(.'+$(this).val()+')').hide();
    });
    $('#requlist tbody tr:not(.'+$('select[name="database"').val()+')').hide();

</script>