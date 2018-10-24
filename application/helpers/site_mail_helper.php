<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 10/30/14
 * Time: 12:26 PM
 */
//todo migration to mailchimp
function get_site_mail_info($mail_id, $param)
{
    $ci =& get_instance();

    $ci->load->model('site_mail_m');
    //get all adverts
    return $ci->site_mail_m->get_info($mail_id, $param);
}

function get_inbox()
{
    $ci =& get_instance();

    $ci->load->model('site_mail_m');

    //get all adverts
    return $ci->site_mail_m->get_inbox();
}

function get_unread_site_mail()
{
    $ci =& get_instance();

    $ci->load->model('site_mail_m');

    //get all adverts
    return $ci->site_mail_m->get_unread();
}

function send_html_email($to, $subject = '', $salutation, $content, $from, $cc = '')
{
    if (check_live_server()) {
        $to = $to;

        $subject = $subject;

        $headers = "From: " . strip_tags($from) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($from) . "\r\n";
        if ($cc) {
            $headers .= "CC:" . $cc . "\r\n";
        }

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $message = '<html>';
        $message .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
    <head>
        <meta name="viewport" content="width=device-width"/>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';
        $message .= '<title>' . $subject . '</title>';

        $message .= '<style type="text/css">
            img {
                max-width: 100%;
            }

            body {
                -webkit-font-smoothing: antialiased;
                -webkit-text-size-adjust: none;
                width: 100% !important;
                height: 100%;
                line-height: 1.6;
            }

            body {
                background-color: #f6f6f6;
            }

            @media only screen and (max-width: 640px) {
                h1 {
                    font-weight: 600 !important;
                    margin: 20px 0 5px !important;
                }

                h2 {
                    font-weight: 600 !important;
                    margin: 20px 0 5px !important;
                }

                h3 {
                    font-weight: 600 !important;
                    margin: 20px 0 5px !important;
                }

                h4 {
                    font-weight: 600 !important;
                    margin: 20px 0 5px !important;
                }

                h1 {
                    font-size: 22px !important;
                }

                h2 {
                    font-size: 18px !important;
                }

                h3 {
                    font-size: 16px !important;
                }

                .container {
                    width: 100% !important;
                }

                .content {
                    padding: 10px !important;
                }

                .content-wrap {
                    padding: 10px !important;
                }

                .invoice {
                    width: 100% !important;
                }
            }
        </style>
    </head>';

        $message .= '<body itemscope itemtype="http://schema.org/EmailMessage"
          style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; background: #f6f6f6; margin: 0;">';

        $message .= '<img src="' . base_url() . 'images/logo.png" alt="' . $subject . '" />';
        $message .= '<body itemscope itemtype="http://schema.org/EmailMessage"
          style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; background: #f6f6f6; margin: 0;">

    <table class="body-wrap"
           style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px;  background: #f6f6f6; margin: 0;">
        <tr style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
            <td style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;"
                valign="top"></td>
            <td class="container" width="600"
                style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;"
                valign="top">
                <div class="content"
                     style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                    <table class="main"  cellpadding="0" cellspacing="0" itemprop="action" itemscope
                           itemtype="http://schema.org/ConfirmAction"
                           style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; background: #fff; margin: 0; border: 1px solid #e9e9e9;">
                           <tr style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
						<td class="alert alert-primary" style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 16px; vertical-align: top; color: #fff; font-weight: 500; text-align: center; border-radius: 3px 3px 0 0; background: #FF9F00; margin: 0; padding: 20px;" align="center" valign="top">
							' . $subject . '
						</td>
					</tr>
                        <tr style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                            <td class="content-wrap"
                                style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;"
                                valign="top">
                                <meta itemprop="name" content="Confirm Email"
                                      style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"/>
                                <table width="100%" cellpadding="0" cellspacing="0"
                                       style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                    <tr style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <td class="content-block"
                                            style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;"
                                            valign="top">';

        $message .= $salutation;

        $message .= '</td>
                                    </tr>
                                    <tr style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <td class="content-block"
                                            style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;"
                                            valign="top">
                                            ' . $content . '
                                        </td>
                                    </tr>

                                    <tr style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <td class="content-block"
                                            style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;"
                                            valign="top">
                                            &mdash; ' . $from . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer"
                         style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                        <table width="100%"
                               style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                            <tr style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                <td><?= base_url() ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="font-family:  Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;"
                valign="top"></td>
        </tr>
    </table>

    </body>
    </html>';

        //echo $message;


        if (mail($to, $subject, $message, $headers)) {
            return TRUE;
        } else {
            //if not sent
            echo error_template('Mail was not sent');
            return FALSE;
        }

    } else {
        //if on localhost return true
        return TRUE;
    }


}

function send_html_email_no_template($to, $subject = '',  $content, $from, $cc = '')
{
    if (check_live_server()) {
        $to = $to;

        $subject = $subject;

        $headers = "From: " . strip_tags($from) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($from) . "\r\n";
        if ($cc) {
            $headers .= "CC:" . $cc . "\r\n";
        }

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        if (mail($to, $subject, $content, $headers)) {
            return TRUE;
        } else {
            //if not sent
            echo error_template('Mail was not sent');
            return FALSE;
        }

    } else {
        //if on localhost return true
        return TRUE;
    }


}


function action_html_template_wrap($content)
{


    $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
<head>
<meta name="viewport" content="width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Actionable emails e.g. reset password</title>


<style type="text/css">
img {
max-width: 100%;
}
body {
-webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6;
}
body {
background-color: #f6f6f6;
}
@media only screen and (max-width: 640px) {
  body {
    padding: 0 !important;
  }
  h1 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h2 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h3 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h4 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h1 {
    font-size: 22px !important;
  }
  h2 {
    font-size: 18px !important;
  }
  h3 {
    font-size: 16px !important;
  }
  .container {
    padding: 0 !important; width: 100% !important;
  }
  .content {
    padding: 0 !important;
  }
  .content-wrap {
    padding: 10px !important;
  }
  .invoice {
    width: 100% !important;
  }
}
</style>
</head>

<body itemscope itemtype="http://schema.org/EmailMessage" style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; background: #f6f6f6; margin: 0;">

<table class="body-wrap" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background: #f6f6f6; margin: 0;">
	<tr style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
		<td style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
		<td class="container" width="600" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;" valign="top">
			<div class="content" style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
				<table class="main" width="100%" cellpadding="0" cellspacing="0" itemprop="action" itemscope itemtype="http://schema.org/ConfirmAction" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; background: #fff; margin: 0; border: 1px solid #e9e9e9;">
					<tr style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
						<td class="content-wrap" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;" valign="top">
							<meta itemprop="name" content="Confirm Email" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;" />
							<table width="100%" cellpadding="0" cellspacing="0" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
								<tr style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
									<td class="content-block" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
										' . $content . '
									</td>
								</tr>



							</table>
						</td>
					</tr>
				</table>
				<div class="footer" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
					<table width="100%" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
						<tr style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
							<td class="aligncenter content-block" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; vertical-align: top; text-align: center; margin: 0; padding: 0 0 20px;" align="center" valign="top"><a href="' . base_url() . '" style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0;"></a> ' . SITE_NAME . ' .</td>
						</tr>
					</table>
				</div></div>
		</td>
		<td style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
	</tr>
</table>

</body>
</html>
';


    return $message;


}


function action_email_template_wrap($to, $subject, $content, $from, $action_link)
{

    if (check_live_server()) {
        $to = $to;

        $subject = $subject;

        $headers = "From: " . strip_tags($from) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($from) . "\r\n";


        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
<head>
<meta name="viewport" content="width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Actionable emails e.g. reset password</title>


<style type="text/css">
img {
max-width: 100%;
}
body {
-webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6;
}
body {
background-color: #f6f6f6;
}
@media only screen and (max-width: 640px) {
  body {
    padding: 0 !important;
  }
  h1 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h2 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h3 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h4 {
    font-weight: 800 !important; margin: 20px 0 5px !important;
  }
  h1 {
    font-size: 22px !important;
  }
  h2 {
    font-size: 18px !important;
  }
  h3 {
    font-size: 16px !important;
  }
  .container {
    padding: 0 !important; width: 100% !important;
  }
  .content {
    padding: 0 !important;
  }
  .content-wrap {
    padding: 10px !important;
  }
  .invoice {
    width: 100% !important;
  }
}
</style>
</head>

<body itemscope itemtype="http://schema.org/EmailMessage" style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; background: #f6f6f6; margin: 0;">

<table class="body-wrap" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background: #f6f6f6; margin: 0;">
	<tr style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
		<td style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
		<td class="container" width="600" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;" valign="top">
			<div class="content" style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
				<table class="main" width="100%" cellpadding="0" cellspacing="0" itemprop="action" itemscope itemtype="http://schema.org/ConfirmAction" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; background: #fff; margin: 0; border: 1px solid #e9e9e9;">
					<tr style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
						<td class="content-wrap" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 20px;" valign="top">
							<meta itemprop="name" content="Confirm Email" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;" />
							<table width="100%" cellpadding="0" cellspacing="0" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
								<tr style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
									<td class="content-block" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
										' . $content . '
									</td>
								</tr>

								<tr style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
									<td class="content-block" itemprop="handler" itemscope itemtype="' . $action_link . '" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
										<a href="' . $action_link . '" itemprop="url" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">' . $subject . ' </a>
									</td>
								</tr>

							</table>
						</td>
					</tr>
				</table>
				<div class="footer" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
					<table width="100%" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
						<tr style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
							<td class="aligncenter content-block" style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; vertical-align: top; text-align: center; margin: 0; padding: 0 0 20px;" align="center" valign="top"><a href="' . base_url() . '" style="font-family:' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0;"></a> ' . SITE_NAME . ' .</td>
						</tr>
					</table>
				</div></div>
		</td>
		<td style="font-family: ' . 'Helvetica Neue' . ', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
	</tr>
</table>

</body>
</html>
';


        if (mail($to, $subject, $message, $headers)) {
            return TRUE;
        } else {
            //if not sent
            echo error_template('Mail was not sent');
            return FALSE;
        }

    } else {
        //if on localhost return true
        return TRUE;
    }

}
