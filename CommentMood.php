<?php
/*
Plugin Name: CommentMood
Plugin URI: http://blog.webenbank.fr/commentmood-plugin
Description: Ce plugin permet aux personnes qui &eacute;crivent des commentaires d'indiquer leur avis par rapport &agrave; l'opinion de l'article. Les lecteurs peuvent ainsi avoir une id&eacute;e visuel de l'humeur g&eacute;n&eacute;rale qui se d&eacute;gage d'une discussion.
Version: 2.0
Author: Benjamin FELLOUS
Author URI: http://blog.webenbank.fr
*/

$commentmood_version = "1.0.0";
//Fonction pour initialiser le plugin
function plugin_install()
{
    global $wpdb;
    $table = $wpdb->prefix."commentMood";
    $structure = "CREATE TABLE $table (
	post_ID BIGINT(20) NOT NULL,
        comment_ID BIGINT(20) NOT NULL,
        mood_value int(1) NOT NULL,
	UNIQUE (
		post_ID, comment_ID
	)
    );";
    $wpdb->query($structure);
    $table = $wpdb->prefix."commentMood_posts";
    $structure = "CREATE TABLE $table (
	post_ID BIGINT(20) NOT NULL,
        UNIQUE (
		post_ID
	)
    );";
    $wpdb->query($structure);

    if (!get_option('commentmood_version')) {
	add_option("commentmood_version", $commentmood_version);
    } else {
	update_option("commentmood_version", $commentmood_version);
    }
}

//initialise le plugin
add_action('activate_wp-content/plugins/commentMood/CommentMood.php', 'plugin_install');

//On initialise la variable current_comment_id
function set_current_comment_id($id_current){
	global $current_comment_id; 
	$current_comment_id = $id_current;
        $table = $wpdb->prefix."comments";
	return $current_comment_id;
}
add_filter('get_comment_ID','set_current_comment_id');

//Fonction pour l'affichage du commentaire
function rendu_comments($content){
        if(is_commentmood_deactivated_for_this_post(get_the_guid()) == false)
              return $content;
	global $current_comment_id;
	global $wpdb;
    	$table = $wpdb->prefix."commentMood";
    	$current_ambiance = $wpdb->get_var("SELECT mood_value FROM $table WHERE comment_ID = ".get_comment_ID()." ;");
        $length = strlen(get_option('textGood')) * 10;

	if($current_ambiance == 1){ 
             if(get_option('visibility_type') == 'text-decoration')
                  $content="<font color='".get_option('colorGood')."'>".$content."</font>";        
             if(get_option('visibility_type') == 'comment-decoration')
                  $content="<div style='background-color:".get_option('colorGood')."'>".$content."</div>"; 
             if(get_option('visibility_type') == 'pastilleTop-decoration')
                  $content="<div style='text-align:center;color:white;width:".$length."px;background-color:".get_option('colorGood').";'>".get_option('textGood')."</div><br />".$content;
             if(get_option('visibility_type') == 'pastilleBottom-decoration')
                  $content=$content."<br /><div style='text-align:center;color:white;width:".$length."px;background-color:".get_option('colorGood').";'>".get_option('textGood')."</div>";
        }
        if($current_ambiance == 2) {
             if(get_option('visibility_type') == 'text-decoration')
                  $content="<font color='".get_option('colorBad')."'>".$content."</font>";      
             if(get_option('visibility_type') == 'comment-decoration')
                  $content="<div style='background-color:".get_option('colorBad')."'>".$content."</div>";
             if(get_option('visibility_type') == 'pastilleTop-decoration')
                  $content="<div style='text-align:center;color:white;width:".$length."px;background-color:".get_option('colorBad').";'>".get_option('textBad')."</div><br />".$content;
             if(get_option('visibility_type') == 'pastilleBottom-decoration')
                  $content=$content."<br /><div style='text-align:center;color:white;width:".$length."px;background-color:".get_option('colorBad').";'>".get_option('textBad')."</div>";
        }
        if($current_ambiance == NULL) {
             if(get_option('visibility_type') == 'text-decoration')
                  $content="<font color='".get_option('colorNeutre')."'>".$content."</font>";      
             if(get_option('visibility_type') == 'comment-decoration')
                  $content="<div style='background-color:".get_option('colorNeutre')."'>".$content."</div>";
             if(get_option('visibility_type') == 'pastilleTop-decoration')
                  $content="<div style='text-align:center;color:white;width:".$length."px;background-color:".get_option('colorNeutre').";'> ".get_option('textNeutre')."</div><br />".$content;
             if(get_option('visibility_type') == 'pastilleBottom-decoration')
                  $content=$content."<br /><div style='text-align:center;color:white;width:".$length."px;background-color:".get_option('colorNeutre').";'>".get_option('textNeutre')."</div>";
        }    
        return $content;
}
add_filter('get_comment_text','rendu_comments');

//Pour afficher le formulaire d'humeur
function commentMood_radio_form()
{
	global $post;
	$textGood = get_option('textGood');
	$textBad = get_option('textBad');
	$textNeutre = get_option('textNeutre');
	$colorGood = get_option('colorGood');
	$colorBad = get_option('colorBad');
	$colorNeutre = get_option('colorNeutre');	

	$html = "
	   <div style='visibility:hidden' id='commentMoodradioform'>	
                <label for='n_commentMood_post_comment'>".__(get_option('textArticle'), 'moodlight')." :</label>
		<div class='commentMood_form_for_post_comment'>
			<div class='commentMood_color_box_for_comment' style='background-color:".$colorBad." ;'></div> 
			<div class='commentMood_label_for_post_comment' id='".$textBad."label' >
				<input type='radio' value='2' name='radioMood' id='".$textBad."radio'/>
				<label for='badMood'>".$textBad."</label>
			</div>
			<div class='commentMood_color_box_for_comment' style='background-color:".get_option('colorNeutre')." ;'></div> 
			<div class='commentMood_label_for_post_comment' id='".$textNeutre."label' >
				<input type='radio' value='0' name='radioMood' id='".$textNeutre."radio'/>
				<label for='neutralMood'>".$textNeutre."</label>
			</div>
			<div class='commentMood_color_box_for_comment' style='background-color:".get_option('colorGood')." ;'></div> 
			<div class='commentMood_label_for_post_comment' id='".$textGood."label' >
				<input type='radio' value='1'  name='radioMood' id='".$textGood."radio'/>
				<label for='goodMood'>".$textGood."</label>
			</div>
		</div>
           </div><br /><br />";
      echo $html;
?>
<script>
      document.getElementById('commentMoodradioform').style.visibility="visible";
      document.getElementById('<?php echo get_option('textBad'); ?>label').style.width="85px";
      document.getElementById('<?php echo get_option('textNeutre'); ?>label').style.width="85px";
      document.getElementById('<?php echo get_option('textGood'); ?>label').style.width="85px";
      document.getElementById('<?php echo get_option('textBad'); ?>radio').style.width="17px";
      document.getElementById('<?php echo get_option('textNeutre'); ?>radio').style.width="17px";
      document.getElementById('<?php echo get_option('textGood'); ?>radio').style.width="17px";
</script>
<?php
}

function commentMood_choose_mood() {
	global $wp_query;
        if(is_commentmood_deactivated_for_this_post(get_the_guid()) == false)
              return ;
        if(get_option('form_type')=='button'){
                ?>
                <script>
                        document.getElementById('submit').style.visibility='hidden';
                </script>
                <?php
                echo get_option('textArticle').":<br />
                     <input name='submit' type='submit' id='ambiance' value='".get_option('textGood')."' style='background:".get_option('colorGood').";width:70px;'/>
                     <input name='submit' type='submit' id='ambiance' value='".get_option('textBad')."'  style='background:".get_option('colorBad').";width:70px;'/>
                     <input name='submit' type='submit' id='ambiance' value='".get_option('textNeutre')."'  style='background:".get_option('colorNeutre').";width:70px;'/>
                ";

        }
        else commentMood_radio_form();
}

add_action('comment_form', 'commentMood_choose_mood');

function commentMood_css()
{
		echo '
			<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/commentMood/commentMood.css" type="text/css" media="screen" />
		';
}
//add_action('wp_head', 'commentMood_css');
add_action('get_header', 'commentMood_css');

//Enregistrement de l'humeur du commentaire
function before_save_comment($content){
         if(is_commentmood_deactivated_for_this_post($_POST['comment_post_ID']) == false)
                return $content;
         $ambiance = 0;
         global $wpdb;
         $table = $wpdb->prefix."comments";
         $query = $wpdb->get_row("SHOW TABLE STATUS LIKE '".$wpdb->prefix."comments'");
	 $table = $wpdb->prefix."commentMood";
         $good_post_id = $_POST['comment_post_ID'];
         $requete ="";
         if($_POST['submit'] == get_option('textGood') || $_POST['radioMood'] == 1){
         	$ambiance = 1;
         	$requete = "INSERT INTO $table(post_ID,comment_ID,  mood_value) VALUES($good_post_id, $query->Auto_increment, $ambiance)";
	 	$wpdb->query($requete);  
         }
         if($_POST['submit'] == get_option('textBad') || $_POST['radioMood'] == 2){
               $ambiance = 2;
               $requete = "INSERT INTO $table(post_ID, comment_ID,  mood_value) VALUES($good_post_id,$query->Auto_increment, $ambiance)";
	       $wpdb->query($requete);
	 }
         
         return  $content;
}
add_filter('pre_comment_content','before_save_comment');

//Humeur generale de l'article
function display_general_mood($content){
        if(is_commentmood_deactivated_for_this_post(get_the_guid()) == false)
              return $content;
	$chart = get_chart(get_the_guid());
        if(StartsWith($chart,'Pas de'))
            return $content."<br />".$chart."<br />";
        $sort = "Trier les commentaires : <a href='".get_permalink( $post->ID )."?commentMoodSort=1'>Humeur</a>  |  <a href='".get_permalink( $post->ID )."?commentMoodSort=0'>Normal</a><br />";
       	$stats = "<br />Humeur g&eacute;n&eacute;rale : <br />".$chart."<br />";
        return $content.$stats.$sort ;
}
add_filter('the_content','display_general_mood');

function commentMood_sortComments($comments){
        if(isset($_GET['commentMoodSort'])){
               if($_GET['commentMoodSort'] == 1){
			global $wpdb;
        	        $table = $wpdb->prefix."commentMood";
        	        $postiveCommentsArray = array();
        	        $neutreCommentsArray = array();
        	        $negativeCommentsArray = array();
	 
	                for($i=0;$i<count($comments);$i++){
                                $idcomment = $comments[$i]->comment_ID;
	                        $requete = "SELECT mood_value FROM $table WHERE comment_ID = $idcomment;";
	                        $commentmoodvalue = $wpdb->get_var($requete);

	                        if($commentmoodvalue == 1)
                                	array_push($postiveCommentsArray ,$comments[$i]);
	                        if($commentmoodvalue == 2)
                                	array_push($negativeCommentsArray ,$comments[$i]);
	                        if($commentmoodvalue == NULL)
                                	array_push($neutreCommentsArray ,$comments[$i]);   
	                } 
	                
                        $newComments = array_merge($postiveCommentsArray,$negativeCommentsArray,$neutreCommentsArray );
                        
                        return $newComments;
                        

	      }
        }
        return $comments;
}
add_filter('comments_array','commentMood_sortComments');


//POUR AJOUTER LA CONFIG DANS LES OPTIONS
add_action('admin_menu', 'commentMood_menu');

//Gestion des options
function commentMood_menu() {
  add_options_page('Comment Mood', 'Comment Mood', 8, 'CommentMood.php', 'commentMood_options');
  add_option('colorGood', '#4CB84E');
  add_option('colorBad', '#DB5331');
  add_option('colorNeutre', '#B68319');
  add_option('textGood', 'Positif');
  add_option('textNeutre', 'Neutre');
  add_option('chartType', 'bar');
  add_option('textBad', 'N&eacute;gatif');
  add_option('textArticle', 'Humeur g&eacute;n&eacute;rale');

  add_option('form_type','button');
  add_option('visibility_type', 'text-decoration');

  if ($_REQUEST['restore']) {
	restore_form();
  } else if ($_REQUEST['save']) {
		if (!$_REQUEST['colorGood'])
			$_REQUEST['colorGood'] = "";
		update_option('colorGood', $_REQUEST['colorGood']);

		if (!$_REQUEST['colorBad'])
			$_REQUEST['colorBad'] = "";
		update_option('colorBad', $_REQUEST['colorBad']);

		if (!$_REQUEST['colorNeutre'])
			$_REQUEST['colorNeutre'] = "";
		update_option('colorNeutre', $_REQUEST['colorNeutre']);

		if (!$_REQUEST['textGood'])
			$_REQUEST['textGood'] = "";
		update_option('textGood', $_REQUEST['textGood']);

		if (!$_REQUEST['textNeutre'])
			$_REQUEST['textNeutre'] = "";
		update_option('textNeutre', $_REQUEST['textNeutre']);

		if (!$_REQUEST['textBad'])
			$_REQUEST['textBad'] = "";
		update_option('textBad', $_REQUEST['textBad']);

		if (!$_REQUEST['textArticle'])
			$_REQUEST['textArticle'] = "";
		update_option('textArticle', $_REQUEST['textArticle']);
		
		if (!$_REQUEST['chart_type'])
			$_REQUEST['chart_type'] = "";
		update_option('chartType', $_REQUEST['chart_type']);

		if (!$_REQUEST['form_type'])
			$_REQUEST['form_type'] = "";
		update_option('form_type', $_REQUEST['form_type']);

		if (!$_REQUEST['visibility_type'])
			$_REQUEST['visibility_type'] = "";
		update_option('visibility_type', $_REQUEST['visibility_type']);
	}
}

//Restaurer les options par d&eacute;faut
function restore_form(){
	update_option('colorGood', '#4CB84E');
  	update_option('colorBad', '#DB5331');
	update_option('colorNeutre', '#B68319');
	update_option('chartType', 'bar');
        update_option('form_Type', 'button');
        update_option('visibility_type', 'text-decoration');
  	update_option('textGood', 'Positif');
  	update_option('textNeutre', 'Neutre');
  	update_option('textBad', 'N&eacute;gatif');
  	update_option('textArticle', 'Humeur g&eacute;n&eacute;rale');
}

//Formulaire des options
function commentMood_options() {
?>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    	<div class='wrap'>
	<h2>Configuration de CommentMood</h2>
	<table class='form-table'>
		<tr>
			<th scope="row" valign="top">
				Couleur d'&eacute;valuation positive :
			</th>
			<td>
				<div class="controlset">

					<input id="colorGood" type="text" name="colorGood" value="<?php echo get_option('colorGood') ?>" /> 
					<?php echo "<input type='text' disabled='disabled' size='3' style='background: ".get_option('colorGood')."' />"; ?>

				</div>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Couleur d'&eacute;valuation n&eacute;gative:
			</th>
			<td>
				<div class="controlset">
					<input id="colorBad" type="text" name="colorBad" value="<?php echo get_option('colorBad'); ?>" />
					<?php echo "<input type='text' disabled='disabled' size='3' style='background: ".get_option('colorBad')."' />"; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Couleur d'&eacute;valuation neutre:
			</th>
			<td>
				<div class="controlset">
					<input id="colorNeutre" type="text" name="colorNeutre" value="<?php echo get_option('colorNeutre'); ?>" />
					<?php echo "<input type='text' disabled='disabled' size='3' style='background: ".get_option('colorNeutre')."' />"; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Texte &eacute;valuation positive :
			</th>
			<td>
				<div class="controlset"><input id="textGood" type="text" name="textGood" value="<?php echo get_option('textGood') ?>" /></div>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Texte &eacute;valuation n&eacute;gative:
			</th>
			<td>
				<div class="controlset"><input id="textBad" type="text" name="textBad" value="<?php echo get_option('textBad'); ?>" /></div>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Texte &eacute;valuation neutre:
			</th>
			<td>
				<div class="controlset"><input id="textNeutre" type="text" name="textNeutre" value="<?php echo get_option('textNeutre'); ?>" /></div>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Texte d'&eacute;valuation d'un article:
			</th>
			<td>
				<div class="controlset"><input id="textArticle" type="text" name="textArticle" value="<?php echo get_option('textArticle') ?>" /></div>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Type de formulaire:
			</th>
			<td>
				<input id="form_type" name="form_type" type="radio" value="radio" <?php if(get_option('form_type')=='radio') echo 'checked'; ?> />
                                <label>3 radio bouttons.</label> <br />
				<input id="form_type" name="form_type" type="radio" value="button" <?php if(get_option('form_type')=='button') echo 'checked'; ?> />
                                <label>3 bouttons aux couleurs différentes.</label>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Visibilité de l'humeur dans le commentaire:
			</th>
			<td>
				<input id="visibility_type" name="visibility_type" type="radio" 
                                                            value="text-decoration" 
                                                            <?php if(get_option('visibility_type')=='text-decoration') echo 'checked'; ?> />
                                <label>L'humeur est appliqué à la couleur de texte du commentaire.</label> <br />
				<input id="visibility_type" name="visibility_type" type="radio" 
                                                            value="comment-decoration" 
                                                            <?php if(get_option('visibility_type')=='comment-decoration') echo 'checked'; ?> />
                                <label>L'humeur est appliqué à la couleur de fond du commentaire.</label> <br />
				<input id="visibility_type" name="visibility_type" type="radio" 
                                                            value="pastilleTop-decoration" 
                                                            <?php if(get_option('visibility_type')=='pastilleTop-decoration') echo 'checked'; ?> />
                                <label>L'humeur est visible grâce à une annotation au dessus du commentaire.</label> <br />
				<input id="visibility_type" name="visibility_type" type="radio" 
                                                            value="pastilleBottom-decoration" 
                                                            <?php if(get_option('visibility_type')=='pastilleBottom-decoration') echo 'checked'; ?> />
                                <label>L'humeur est visible grâce à une annotation en dessous du commentaire.</label> <br />
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				Type de graphique:
			</th>



			<td>
				<div class="controlset">
					<input id="chart_type" name="chart_type" type="radio" value="gom" <?php if(get_option('chartType') == 'gom') echo 'checked'; ?> /> <img src='http://chart.apis.google.com/chart?cht=gom&chs=200x100&chd=t:60&chl=Tendance&chco=<?php echo substr(get_option('colorBad'),1); ?>,<?php echo substr(get_option('colorNeutre'),1); ?>,<?php echo substr(get_option('colorGood'),1); ?>' /><br />
					<input id="chart_type" name="chart_type" type="radio" value="pie" <?php if(get_option('chartType') == 'pie') echo 'checked'; ?> /> <img src='http://chart.apis.google.com/chart?cht=p&chd=t:20,50,30&chs=200x80&chl="<?php get_option('textNeutre'); ?>"|Positif|Negatif&chco=FEF693|4CB84E|DB5331' /><br />
					<input id="chart_type" name="chart_type" type="radio" value="bar" <?php if(get_option('chartType') == 'bar') echo 'checked'; ?> /> <img src='http://chart.apis.google.com/chart?cht=bhs&chs=200x60&chd=t:50,20,70&chxt=x,y&chxl=1:|"<?php get_option('textNeutre'); ?>"|Positif|Negatif&chxr=0,0,100&chbh=8,1,0&chco=FEF693|4CB84E|DB5331' />
				</div>
			</td>
		</tr>
			<td>&nbsp;</td>
			<td>
				<span class="submit"><input name="save" value="<?php _e("Enregistrer les changements", 'ambiance'); ?>" type="submit" /></span>
				<span class="submit"><input name="restore" value="<?php _e("Options par defaut", 'ambiance'); ?>" type="submit"/></span><br/>
			</td>
		</tr>
	</table>
	</div>
	</form>

<?php

}


//A la suppression d'un commentaire
function before_delete_comment($id_comment){
	global $wpdb;
	$table = $wpdb->prefix."commentMood";
	$requete = "DELETE FROM $table WHERE comment_ID = $id_comment;";
        $wpdb->query($requete);
}
add_action('delete_comment', 'before_delete_comment'); 

function StartsWith($Haystack, $Needle){
    // Recommended version, using strpos
    return strpos($Haystack, $Needle) === 0;
}

function get_chart($post_guid){
	global $wpdb;
        $positiveMood = 0;
	$negativeMood = 0;
	$netralMood = 0;
        $table = $wpdb->prefix."comments";
        $post_table = $wpdb->prefix."posts";
        if(StartsWith($post_guid,'http:'))
             $good_post_id = $wpdb->get_var("SELECT ID FROM $post_table WHERE guid = '$post_guid';");
        else $good_post_id = $post_guid;
        $commentsCount = $wpdb->get_var("SELECT comment_count as myCount FROM $post_table WHERE ID = $good_post_id;");
	$table = $wpdb->prefix."commentMood";
	$positiveMood  = $wpdb->get_var("SELECT COUNT(*) as myCount FROM $table WHERE post_ID = $good_post_id AND mood_value = 1;");
	$negativeMood  = $wpdb->get_var("SELECT COUNT(*) as myCount FROM $table WHERE post_ID = $good_post_id AND mood_value = 2;");
        $netralMood = $commentsCount - $positiveMood - $negativeMood;
	if($commentsCount == 0) 
            $display = "Pas de commentaire pour le moment.";
	else{
	if(get_option('chartType') == "bar"){
		$percentGood = $positiveMood/$commentsCount*100;
		$percentBad = $negativeMood/$commentsCount*100;
		$percentNetral = $netralMood/$commentsCount*100;
		$url_img= "http://chart.apis.google.com/chart?cht=bhs&chs=200x60&chd=t:".$percentBad.",".$percentGood.",".$percentNetral."&chxt=x,y&chxl=1:|".get_option('textNeutre')."|".get_option('textGood')."|".get_option('textBad')."&chxr=0,0,100&chbh=8,1,0&chco=".substr(get_option('colorBad'),1)."|".substr(get_option('colorGood'),1)."|".substr(get_option('colorNeutre'),1); 
		$display = "<img src='".$url_img."' />";
	}
	if(get_option('chartType') == "pie"){
		$percentGood = $positiveMood/$commentsCount*100;
		$percentBad = $negativeMood/$commentsCount*100;
		$percentNetral = $netralMood/$commentsCount*100;
		$url_img= "http://chart.apis.google.com/chart?cht=p&chd=t:".$netralMood.",".$positiveMood.",".$negativeMood."&chs=200x80&chl=".get_option('textNeutre')."|".get_option('textGood')."|".get_option('textBad')."&chco=".substr(get_option('colorNeutre'),1)."|".substr(get_option('colorGood'),1)."|".substr(get_option('colorBad'),1);
		$display = "<img src='".$url_img."' />";
	}
	if(get_option('chartType') == "gom"){
		if($positiveMood == $negativeMood){
			$tendance = $commentsCount / 2;
			$total = $commentsCount;
		}
		else{
			$total = $positiveMood - $negativeMood;
			$percentGood = $positiveMood/$total*100;
			$percentBad = $negativeMood/$total*100;
			if($percentBad > $percentGood)
				$tendance = $percentBad;
			if($percentGood > $percentBad)
				$tendance = $percentGood;
		}
		$url_img= "http://chart.apis.google.com/chart?cht=gom&chs=200x100&chd=t:".$tendance."&chl=Tendance&chds=0,".$total."&chco=".substr(get_option('colorBad'),1).",".substr(get_option('colorNeutre'),1).",".substr(get_option('colorGood'),1);
		$display = "<img src='".$url_img."' />";
	}
        }
        return $display;
}


//Pour afficher la colonne humeur dans l'administration
add_filter('manage_posts_columns', 'add_commentMood_column');
function add_commentMood_column($defaults) {
    $defaults['moodTendance'] = __('Hummeur Generale');
    return $defaults;
}

//Pour afficher les donn?es
add_action('manage_posts_custom_column', 'add_commentMood_data', 10, 2);
function add_commentMood_data($column_name, $post_id) {
    if( $column_name == 'moodTendance' ) {
	$display = get_chart($post_id);
        if( $display ) {
            echo $display;
        }
    }
}

//Pour gérer l'activation dans la page d'écriture d'un article
function commentMood_meta() {
	global $post;
	$commentmoodon = false;
	$commentmoodmeta = get_post_meta($post->ID,'commentmoodon',true);
	if ($commentmoodmeta == "true") {
		$commentmoodon = true;
	}

	?>
	<input type="checkbox" name="commentmoodon" <?php if ($commentmoodon=='true') { echo 'checked="checked"'; } ?>/>Enable CommentMood. 
	<?php
}

//Pour afficher l'activation du plugin dans les pages et les articles
function CommentMoodMetaBox() {
	// Check whether the 2.5 function add_meta_box exists, and if it doesn't use 2.3 functions.
	if ( function_exists('add_meta_box') ) {
		add_meta_box('CommentMood','CommentMood','commentMood_meta','post');
		add_meta_box('CommentMood','CommentMood','commentMood_meta','page');
	}
}
add_action('admin_menu', 'CommentMoodMetaBox');

//Pour activer ou désactiver le plugin (meta)
function commentMood_insert_post($pID) {
	if (isset($_POST['commentmoodon'])) {
		add_post_meta($pID,'commentmoodon',"true", true) or update_post_meta($pID, 'commentmoodon', "true");
	} else {
		add_post_meta($pID,'commentmoodon',"false", true) or update_post_meta($pID, 'commentmoodon', "false");
	}
}
add_action('wp_insert_post', 'commentMood_insert_post');
add_action('wp_update_post', 'commentMood_insert_post');

//Pour savoir si le plugin est activé pour un article donné
function is_commentmood_deactivated_for_this_post($guid){
        global $wpdb;
        $post_table = $wpdb->prefix."posts";
        if(StartsWith($guid,'http:')){
             $good_post_id = $wpdb->get_var("SELECT ID FROM $post_table WHERE guid = '$guid';");
             if(get_post_meta($good_post_id,'commentmoodon',true)=="true")
                  return true;
        }
        else {
             if(get_post_meta($guid,'commentmoodon',true)=="true")
                  return true;
        }
        return false;
}

?>