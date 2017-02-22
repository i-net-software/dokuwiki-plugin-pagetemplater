<?php
/**
 * Imageflow Plugin
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     i-net software <tools@inetsoftware.de>
 * @author     Gerry Weissbach <gweissbach@inetsoftware.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_pagetemplater extends DokuWiki_Syntax_Plugin {

    function getType() { return 'substition'; }
    function getPType() { return 'block'; }
    function getSort() { return 30; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('@@CONTENT@@', $mode, 'plugin_pagetemplater');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {

		return true;
    }            
	
    function render($mode, Doku_Renderer $renderer, $data) {
		$renderer->doc .= "@@CONTENT@@";
		return true;
	}
}
// vim:ts=4:sw=4:et:enc=utf-8: 
