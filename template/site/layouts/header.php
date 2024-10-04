<?defined('BILLINGMASTER') or die;?>
<?if(isset($_SESSION['admin_user']) && !isset($_SESSION['user'])):?>
    <div class="admin_warning_front">
        <?php if(isset($this->extension) && $this->extension == 'forum') {
            echo System::Lang('SYSTEM_ADMIN_MESS2_TO_FORUM');
        } else {
            echo System::Lang('SYSTEM_ADMIN_SESSION_MESS');
        }?>
        <a class="btn-blue-small" href="/admin/logout"><?=System::Lang('QUIT');?></a>
    </div>
<?endif;

if(System::hasError()):?>
    <div class="error-message"><?=System::showError();?></div>
<?endif;?>

<header class="header header__pressed-footer<?if($this->settings['fix_head'] == 1) echo ' header-sticky';?>">
  <div class="layout">
    <div class="header__inner">
      <div class="logo">
        <a href="<?=$this->settings['script_url'];?>"><img
          src="<?=$this->settings['logotype'];?>" alt=""></a>
            <?if(!empty($this->main_settings['slogan'])):?>
                <span class="slogan"><?=$this->main_settings['slogan'];?></span>
            <?endif;?>
      </div>
      <?require_once ("{$this->layouts_path}/top.php");?>
    </div>
  </div>
</header>