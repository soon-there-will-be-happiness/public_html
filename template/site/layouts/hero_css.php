<?php defined('BILLINGMASTER') or die;?>
<style>
    .hero-wrap{
        min-height: 300px;
        height: <?=$params['heroheigh'];?>px;
        background-position: <?=$params['position']?>;
        background-size: cover;
    }
    .hero-wrap:before{
        opacity:<?=$params['overlay']?>;
        background:<?=$params['overlaycolor']?>;
    }
    
    .hero_header.h1 {color: <?=$params['color']?>; font-size: <?=$params['fontsize']?>px; }
    
    @media screen and (max-width: 640px),
    only screen and (max-device-width:640px) {
        .hero-wrap {height: <?=$params['heromobileheigh'];?>px}
        .hero_header.h1 {font-size: <?=$params['fontsize_mobile'];?>px}
    }
</style>