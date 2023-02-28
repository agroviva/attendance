<?php
/**
 * attendance - ui:.
 *
 * @link http://www.agroviva.de
 *
 * @author Enver Morinaj
 * @copyright (c) 2015-16 by Agroviva GmbH <info@agroviva.de>
 * @license ---- GPL - GNU General Public License
 *
 * @version $Id: class.attendance_ui.inc.php $
 */
use Attendance\Contract;
use Attendance\Contracts;
use Attendance\Graph;
use EGroupware\Api;
use EGroupware\Api\Framework;
use EGroupware\Api\Header\ContentSecurityPolicy as CSP;

require EGW_INCLUDE_ROOT.'/attendance/app/api/app.php';

class attendance_ui
{
	public $public_functions = [
		'index'             => true,
		'manage'            => true,
		'management'        => true,
		'sync'              => true,
		'attendance_ui'     => true,
	];

	public $tpl;
	private $tmpl;
	private $html;
	private $db;

	private $so;
	private $bo;
	private $synchron;
	private $Contracts;

	public function __construct($egroupware = true)
	{
		$GLOBALS['egw_info']['flags']['app_header'] = 'attendance';

		include_once APPDIR.'/inc/class.attendance_so.inc.php';
		$this->so = new attendance_so();

		include_once APPDIR.'/inc/class.attendance_bo.inc.php';
		$this->bo = new attendance_bo();

		include_once APPDIR.'/inc/class.attendance_sync.inc.php';
		$this->synchron = new attendance_sync();

		$this->Contracts = new Contracts();

		$this->db = $GLOBALS['egw']->db;
	}

	/* STARTSEITE */

	public function index()
	{
		CSP::add_frame_src(['self', 'https://zeiterfassung.agroviva.net/']);
		Api\Cache::setSession('attendance', 'runMethod', 'index');

		$GLOBALS['egw_info']['flags']['nonavbar'] = false;
		echo $GLOBALS['egw']->framework->header(); ?>	
        	<style type="text/css">form{display: none;}</style>
        	<iframe frameborder="0" name="egw_app_iframe_attendance" class="egw_fw_content_browser_iframe" style="width: 100%; border-width: 0px; height: 100%" src="/egroupware/attendance/graph/tracker/"></iframe>
        <?php

		echo $GLOBALS['egw']->framework->footer();
	}

	public function manage($content = [])
	{
		if ($_SERVER['SERVER_NAME'] == 'e00.agroviva.net') {
			// Graph::Render("contracts");
			// die();
		}
		Api\Cache::setSession('attendance', 'runMethod', 'manage');
		//Include(TEMPLATE.'/manage.php');

		if (isset($content)) {
			if (isset($content['add'])) { // Add Button (Contracts)
				$this->add();

				return;
			} elseif (isset($content['con_table']['delete'])) { // Delete Button
				list($id) = each($content['con_table']['delete']);
				Contract::Delete($id);
			} elseif (isset($content['con_table']['edit'])) { // Edit Button
				list($id) = each($content['con_table']['edit']);
				$this->edit($content = null, $id);

				return;
			} elseif (isset($content['expired'])) {
				$this->so->toogle_disabled_contracts();
			}
		}

		$contracts = $this->Contracts->Load();
		$contracts = array_combine(range(1, count($contracts)), array_values($contracts));
		$content['con_table'] = $contracts;
		echo "<div><script type='text/javascript'>
								window.onload = function(){
									var rows = document.querySelectorAll('span');
									var status, searchText;
									for (var i = 0; i < rows.length; i++) {
									  status = rows[i];
									  searchText = 'Abgelaufen';
									  if (status.textContent == searchText) {
									  	 status.parentNode.parentNode.style.background = '#ffbbbb';
									  	 status.parentNode.parentNode.style.opacity = '0.5';
									  }
									}
								}
								</script></div>";
		$GLOBALS['egw_info']['flags']['app_header'] = lang('attendance - Manage');

		$tpl = new etemplate('attendance.manage.index');
		$tpl->set_cell_attribute('debuginfos', 'enabled', !$debug);
		$tpl->exec('attendance.attendance_ui.manage', $content);
	}

	public function management()
	{
		Api\Cache::setSession('attendance', 'runMethod', 'management');
		$GLOBALS['egw_info']['flags']['nonavbar'] = false;
		echo $GLOBALS['egw']->framework->header();

		if (isset($content)) {
			if (isset($content['add'])) { // Add Button (Contracts)
				$this->add();

				return;
			} elseif (isset($content['con_table']['delete'])) { // Delete Button
				list($id) = each($content['con_table']['delete']);
				Contract::Delete($id);
			} elseif (isset($content['con_table']['edit'])) { // Edit Button
				list($id) = each($content['con_table']['edit']);
				$this->edit($content = null, $id);

				return;
			} elseif (isset($content['expired'])) {
				$this->so->toogle_disabled_contracts();
			}
		}

		$content[] = $this->Contracts->Load();

		include TEMPLATE.'/contracts.php';

		echo $GLOBALS['egw']->framework->footer();
	}

	public function sync()
	{
		$GLOBALS['egw_info']['flags']['nonavbar'] = false;
		$GLOBALS['egw_info']['flags']['app_header'] = 'attendance - Sync';

		define('SYNC', true);

		$this->synchron->synchron();

		echo $GLOBALS['egw']->framework->footer();
	}

	public function week_hours($day, $value = 'weekly')
	{
		if ($value == 'weekly') {
			$time = 1;
		} elseif ($value == 'twoweekly') {
			$time = 2;
		} elseif ($value == 'threeweekly') {
			$time = 3;
		} elseif ($value == 'fourweekly') {
			$time = 4;
		}

		return $day / $time;
	}

	public function add($content = [])
	{
		Framework::includeJS('/attendance/app/js/contract.js');
		include TEMPLATE.'/add_new_contract.php';

		$tpl = new etemplate('attendance.manage.add');
		$tpl->set_cell_attribute('debuginfos', 'disabled', !$debug);
		$tpl->exec('attendance.attendance_ui.add', $content, $sel_options);
	}

	public function edit($content, $id = false)
	{
		Framework::includeJS('/attendance/app/js/contract.js');
		include TEMPLATE.'/edit_contract.php';

		$tpl = new etemplate('attendance.manage.edit');
		$tpl->set_cell_attribute('debuginfos', 'disabled', !$debug);
		$tpl->exec('attendance.attendance_ui.edit', $content, $sel_options);
	}
}
