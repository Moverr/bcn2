<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 12/6/2014
 * Time: 7:40 AM
 */

function error_template($msg)
{

    ?>
    <div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>
        <p>
            <?= $msg ?>
        </p>
    </div>

<?php
}

function success_template($msg)
{

    ?>
    <div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>
        <p>
            <?= $msg ?>
        </p>
    </div>
<?php
}

function warning_template($msg)
{

    ?>
    <div class="alert alert-warning"><button class="close" data-dismiss="alert">×</button>
        <p>
            <?= $msg ?>
        </p>
    </div>

<?php
}

function info_template($msg)
{

    ?>
    <div class="alert alert-info"><button class="close" data-dismiss="alert">×</button>
        <p>
            <?= $msg ?>
        </p>
    </div>
<?php
}

function bootstrap_panel_basic($content)
{

    ?>
    <div class="panel">
        <div class="panel-body"><?=$content?></div>
    </div>
<?php
}
function bootstrap_panel_primary($content)
{

    ?>
    <div class="panel panel-primary">
        <div class="panel-body"><?=$content?></div>
    </div>
<?php
}

function bootstrap_panel_primary_with_heading($heading,$content)
{

    ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h4><?=$heading?></h4>
            <div class="options">
                <a class="panel-collapse" href="#">
                    <i class="fa fa-chevron-down"></i></a>
            </div>
        </div>
        <div class="panel-body"><?=$content?></div>
    </div>
<?php
}


function text_info_template($msg)
{

    ?>
    <span class="text-info">
        <?=$msg?>
    </span>
<?php
}

function text_danger_template($msg)
{

    ?>
    <span class="text-error">
        <?=$msg?>
    </span>
<?php
}

function text_success_template($msg)
{

    ?>
    <span class="text-success">
        <?=$msg?>
    </span>
<?php
}

function text_warning_template($msg)
{

    ?>
    <span class="text-warning">
        <?=$msg?>
    </span>
<?php
}