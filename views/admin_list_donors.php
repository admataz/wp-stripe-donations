<div class="wrap">
            <h2>Public Suggestions and Support for claims</h2>
        <form id="process-suggestions" method="POST">
                    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $list_table->display() ?>
        </form> 

        </div>



