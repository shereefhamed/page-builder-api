<div class="wrap">
    <h1><?php esc_html_e(get_admin_page_title()) ?></h1>
    <?php $active_tab = isset($_GET['tab']) ? $_GET['tab']: 'api-keys' ?>
    <div class="nav-tab-wraper">
        <a 
            href="?page=page_builder&tab=api-keys" 
            class="nav-tab <?php echo $active_tab=='api-keys'? 'nav-tab-active' : '' ?>"
        >
            API Keys Management
        </a>
        <a 
            href="?page=page_builder&tab=api-activity-log" 
            class="nav-tab <?php echo $active_tab=='api-activity-log'? 'nav-tab-active' : '' ?>"
        >
            API Activity Log
        </a>
        <a 
            href="?page=page_builder&tab=created-page" 
            class="nav-tab <?php echo $active_tab=='created-page'? 'nav-tab-active' : '' ?>"
        >
            Created Pages
        </a>
        <a 
            href="?page=page_builder&tab=settings" 
            class="nav-tab <?php echo $active_tab=='settings'? 'nav-tab-active' : '' ?>"
        >
            Settings
        </a>
        <a 
            href="?page=page_builder&tab=documentation" 
            class="nav-tab <?php echo $active_tab=='documentation'? 'nav-tab-active' : '' ?>"
        >
            Documentation
        </a>
    
    </div>
    <?php 
    if($active_tab==='api-keys') {
        require_once(SIMPLE_PAGE_BUILDER_PATH. 'templates/page-builder-setting-api-keys.php');
    }else if($active_tab==='api-activity-log'){
        require_once(SIMPLE_PAGE_BUILDER_PATH. 'templates/page-builder-setting-api-activity-log.php');
    }else if($active_tab==='created-page'){
        require_once(SIMPLE_PAGE_BUILDER_PATH. 'templates/page-builder-setting-created-pages.php');
    }else if($active_tab==='settings'){
        require_once(SIMPLE_PAGE_BUILDER_PATH. 'templates/page-builder-setting-settings.php');
    }else if($active_tab==='documentation'){
        require_once(SIMPLE_PAGE_BUILDER_PATH. 'templates/page-builder-setting-decumentation.php');
    }
    ?>
</div>