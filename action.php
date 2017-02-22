<?php

/**
 * Select Template Pages for your Content
 * The templates Pages have to have the entry @@CONTENT@@
 * the template per page can be defined using the META plugin
 * 
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     i-net software <tools@inetsoftware.de>
 * @author     Gerry Weissbach <gweissbach@inetsoftware.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

if (!defined('DOKU_LF'))
    define('DOKU_LF', "\n");
if (!defined('DOKU_TAB'))
    define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_PLUGIN . 'action.php');
require_once(DOKU_INC . 'inc/pageutils.php');

class action_plugin_pagetemplater extends DokuWiki_Action_Plugin {

    function getInfo(){
        return array_merge(confToHash(dirname(__FILE__).'/info.txt'), array(
                'name' => 'Page Templater Action Component',
        ));
    }

    /**
     * Register the eventhandlers.
     */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'handle_content_display', array ());
        $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this, 'handle_meta_data', array ());
    }

    function handle_content_display(& $event, $params) {
        global $ACT, $INFO, $TOC, $ID;
        
        $template = $this->resolve_template();
        if ( !$template || $ACT != 'show' ) { return; }
        
        $oldtoc = $TOC;
        $template = p_wiki_xhtml( $template );

        // set the replacements
        $replace = $INFO['meta']['templater'];
        unset($replace['page']);
        $replace['content'] = $event->data;
        $replace['page'] = $ID;
        $replace['namespace'] = getNS($ID);

        $new = $template;
        foreach (array_keys($replace) as $key) {
            if ( $new != $template ) { $template = $new; }
            if ( $key != 'content' && substr($key, 0, 1) == '!' ) {
                $rkey = substr($key, 1);
                $replace[$key] = p_render('xhtml', p_get_instructions($replace[$key]),$info);
            } else { $rkey = $key; }
            $new = str_replace('@@' . strtoupper(trim($rkey)) . '@@', $replace[$key], $template);
            $new = str_replace(urlencode('@@') . strtoupper(trim($rkey)) . urlencode('@@'), $replace[$key], $new);
        }
        
        if ( $new != $event->data ) {
            $event->data = $new;
        }
        
        $TOC = $oldtoc;

        $data = array('xhtml',& $event->data);
        trigger_event('RENDERER_CONTENT_POSTPROCESS',$data);
                
        return true;
    }
    
    function handle_meta_data(& $event, $params) {
        global $ACT;

        $id = getId();
        if ( $id != $event->data['page'] ) { return true; }
        $template = $this->resolve_template( $event->data['current']['templater'] );
        if ( empty( $template) || in_array($template, array( $id, $event->data['page']) ) ) { return true; }

        $meta = p_get_metadata( $template, '', METADATA_RENDER_UNLIMITED );
        
        
        if ( !$event->data['current']['internal'] || !is_array($event->data['current']['internal']) ) $event->data['current']['internal'] = array();
        if ( !is_array($meta['internal']) ) $meta['internal'] = array();
        $event->data['current']['internal'] = array_merge($event->data['current']['internal'], $meta['internal']);

        if ( !$event->data['current']['toc'] || !is_array($event->data['current']['toc']) ) $event->data['current']['toc'] = array();
        if ( !is_array($meta['toc']) ) $meta['toc'] = array();
        $event->data['current']['toc'] = array_merge($event->data['current']['toc'], $meta['toc']);

        return true;
    }

    private function resolve_template( $templater = array() ) {
        global $INFO;
        
        $page = empty($INFO['meta']['templater']['page']) ? $templater['page'] : $INFO['meta']['templater']['page'];
        
        // are we in an avtive Namespace?
        $template = $this->_getActiveNamespace();
        if ( !$template && empty( $page ) ) return;

        return empty( $page ) ? $template : $page;
    }
    
    function _getActiveNamespace() {
        global $ID;
        global $INFO;
        
        $pattern = $this->getConf('excluded_pages');
        if (strlen($pattern) > 0 && preg_match($pattern, $ID)) {
            return false;
        }

        $this->loadPages();
        foreach ($this->pages as $namespace) {
            $space = cleanID($namespace[0]);
            if (trim($space) && (strpos($ID, $space . ':') === 0)) {
                return resolve_id($namespace[0], $namespace[1]);
            }
        }

        return false;
    }

    private static $pages = null;
    private function loadPages() {
        if ( $this->pages != null ) {
            return;
        }
        
        $this->pages = array();
        $namespaces = explode("\n", $this->getConf('enabled_namespaces'));
        foreach( $namespaces as $namespace ) {
            
            if ( strlen(trim($namespace)) == 0 ) { continue; }
            $this->pages[] = explode("=>", $namespace);
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
