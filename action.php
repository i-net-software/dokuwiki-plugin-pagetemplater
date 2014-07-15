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
    }

    function handle_content_display(& $event, $params) {
		global $ACT, $INFO;
		
		if ( $ACT != 'show' )
			return;
			
		// are we in an avtive Namespace?
		$namespace = $this->_getActiveNamespace();
		if (!$namespace && empty($INFO['meta']['templater']['page'])) { return; }
		
		// check for the template
		$template = p_wiki_xhtml(empty ($INFO['meta']['templater']['page']) ? resolve_id($namespace, $this->getConf('templater_page')) : $INFO['meta']['templater']['page'],'',false);
		if ( !$template ) { return; }

		// set the replacements
		$replace = $INFO['meta']['templater'];
		unset($replace['page']);
		$replace['content'] = $event->data;

		$new = $template;
		foreach (array_keys($replace) as $key) {
			if ( $new != $template ) { $template = $new; }
			if ( $key != 'content' && substr($key, 0, 1) == '!' ) {
				$rkey = substr($key, 1);
				$replace[$key] = p_render('xhtml', p_get_instructions($replace[$key]),$info);
			} else { $rkey = $key; }
			$new = str_replace('@@' . strtoupper(trim($rkey)) . '@@', $replace[$key], $template);
		}
		
		if ( $new != $event->data ) {
			$event->data = $new;
		}

		return true;
    }
    
    function _getActiveNamespace() {
    	global $ID;
    	global $INFO;
    	
		if (!$INFO['exists'])
			return false;
		
    	$pattern = $this->getConf('excluded_pages');
		if (strlen($pattern) > 0 && preg_match($pattern, $ID)) {
			return false;
		}

        $namespaces = explode(',', $this->getConf('enabled_namespaces'));
        foreach ($namespaces as $namespace) {
			$namespace = cleanID($namespace);
            if (trim($namespace) && (strpos($ID, $namespace . ':') === 0)) {
                return $namespace;
            }
        }

        return false;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
