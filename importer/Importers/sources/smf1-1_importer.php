<?php
/**
 * @name      OpenImporter
 * @copyright OpenImporter contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * @version 1.0 Alpha
 */

class SMF1_1 extends AbstractSourceImporter
{
	protected $setting_file = '/Settings.php';

	protected $smf_attach_folders = null;

	public function getName()
	{
		return 'SMF1_1';
	}

	public function getVersion()
	{
		return 'ElkArte 1.0';
	}

	public function setDefines()
	{
		define('SMF', 1);
	}

	public function getPrefix()
	{
		$db_name = $this->getDbName();
		$db_prefix = $this->fetchSetting('db_prefix');
		return '`' . $db_name . '`.' . $db_prefix;
	}

	public function getDbName()
	{
		return $this->fetchSetting('db_name');
	}

	public function getTableTest()
	{
		return 'members';
	}

	protected function fetchSetting($name)
	{
		static $content = null;

		if ($content === null)
			$content = file_get_contents($this->path . '/Settings.php');

		$match = array();
		preg_match('~\$' . $name . '\s*=\s*\'(.*?)\';~', $content, $match);

		return isset($match[1]) ? $match[1] : '';
	}

	public function getAttachmentDirs()
	{
		if ($this->smf_attach_folders === null)
		{
			$from_prefix = $this->config->from_prefix;

			$request = $this->db->query("
				SELECT value
				FROM {$from_prefix}settings
				WHERE variable='attachmentUploadDir';");
			list ($smf_attachments_dir) = $this->db->fetch_row($request);

			$this->smf_attach_folders = str_replace('\\', '/', $smf_attachments_dir);
		}

		return $this->smf_attach_folders;
	}

	public function getAttachDir()
	{
		return $this->smf_attach_folders;
	}

	/**
	 * From here on, all the methods are needed helper for the conversion
	 */
	public function preparseAttachments($originalRows)
	{
		$rows = array();
		foreach ($originalRows as $row)
		{
			$ext = strtolower(substr(strrchr($row['filename'], '.'), 1));
			if (!in_array($ext, array('jpg', 'jpeg', 'gif', 'png', 'bmp')))
				$ext = '';

			$row['fileext'] = $ext;
			$row['mime_type'] = '';
			$row['id_folder'] = 0;
			$row['full_path'] = $this->getAttachDir();

			$rows[] = $row;
		}

		return $rows;
	}

	public function codeSettings()
	{
		// @todo this list looks broken (I don't remember any enablePinnedTopics in SMF 1.1)
		$do_import = array(
			'news',
			'compactTopicPagesContiguous',
			'compactTopicPagesEnable',
			'enablePinnedTopics',
			'todayMod',
			'enablePreviousNext',
			'pollMode',
			'enableVBStyleLogin',
			'enableCompressedOutput',
			'attachmentSizeLimit',
			'attachmentPostLimit',
			'attachmentNumPerPostLimit',
			'attachmentDirSizeLimit',
			'attachmentExtensions',
			'attachmentCheckExtensions',
			'attachmentShowImages',
			'attachmentEnable',
			'attachmentEncryptFilenames',
			'attachmentThumbnails',
			'attachmentThumbWidth',
			'attachmentThumbHeight',
			'censorIgnoreCase',
			'mostOnline',
			'mostOnlineToday',
			'mostDate',
			'allow_disableAnnounce',
			'trackStats',
			'userLanguage',
			'titlesEnable',
			'topicSummaryPosts',
			'enableErrorLogging',
			'max_image_width',
			'max_image_height',
			'onlineEnable',
			'smtp_host',
			'smtp_port',
			'smtp_username',
			'smtp_password',
			'mail_type',
			'timeLoadPageEnable',
			'totalMembers',
			'totalTopics',
			'totalMessages',
			'simpleSearch',
			'censor_vulgar',
			'censor_proper',
			'enablePostHTML',
			'enableEmbeddedFlash',
			'xmlnews_enable',
			'xmlnews_maxlen',
			'hotTopicPosts',
			'hotTopicVeryPosts',
			'registration_method',
			'send_validation_onChange',
			'send_welcomeEmail',
			'allow_editDisplayName',
			'allow_hideOnline',
			'guest_hideContacts',
			'spamWaitTime',
			'pm_spam_settings',
			'reserveWord',
			'reserveCase',
			'reserveUser',
			'reserveName',
			'reserveNames',
			'autoLinkUrls',
			'banLastUpdated',
			'avatar_max_height_external',
			'avatar_max_width_external',
			'avatar_action_too_large',
			'avatar_max_height_upload',
			'avatar_max_width_upload',
			'avatar_resize_upload',
			'avatar_download_png',
			'failed_login_threshold',
			'oldTopicDays',
			'edit_wait_time',
			'edit_disable_time',
			'autoFixDatabase',
			'allow_guestAccess',
			'time_format',
			'number_format',
			'enableBBC',
			'max_messageLength',
			'signature_settings',
			'autoOptMaxOnline',
			'defaultMaxMessages',
			'defaultMaxTopics',
			'defaultMaxMembers',
			'enableParticipation',
			'recycle_enable',
			'recycle_board',
			'maxMsgID',
			'enableAllMessages',
			'fixLongWords',
			'who_enabled',
			'time_offset',
			'cookieTime',
			'lastActive',
			'requireAgreement',
			'unapprovedMembers',
			'package_make_backups',
			'databaseSession_enable',
			'databaseSession_loose',
			'databaseSession_lifetime',
			'search_cache_size',
			'search_results_per_page',
			'search_weight_frequency',
			'search_weight_age',
			'search_weight_length',
			'search_weight_subject',
			'search_weight_first_message',
			'search_max_results',
			'search_floodcontrol_time',
			'permission_enable_deny',
			'permission_enable_postgroups',
			'mail_next_send',
			'mail_recent',
			'settings_updated',
			'next_task_time',
			'warning_settings',
			'admin_features',
			'last_mod_report_action',
			'pruningOptions',
			'cache_enable',
			'reg_verification',
			'enable_buddylist',
			'birthday_email',
			'globalCookies',
			'default_timezone',
			'memberlist_updated',
			'latestMember',
			'latestRealName',
			'db_mysql_group_by_fix',
			'rand_seed',
			'mostOnlineUpdated',
			'search_pointer',
			'spider_name_cache',
			'modlog_enabled',
			'disabledBBC',
			'latest_member',
			'latest_real_name',
			'total_members',
			'total_messages',
			'max_msg_id',
			'total_topics',
			'disable_hash_time',
			'latestreal_name',
			'disableHashTime',
		);

		$request = $this->db->query("
			SELECT variable, value
			FROM {$this->config->from_prefix}settings;");

		$rows = array();
		while ($row = $this->db->fetch_assoc($request))
		{
			if (in_array($row['variable'], $do_import))
			{
				$rows[] = array(
					'variable' => $row['variable'],
					'value' => $row['value'],
				);
			}
		}
		$this->db->free_result($request);

		return $rows;
	}

	public function codeAvatars()
	{
		$rows = array();

		$avatarg = $this->db->query("
			SELECT value
			FROM {$this->config->from_prefix}settings
			WHERE variable = 'avatar_directory';");
		list ($smf_avatarg) = $this->db->fetch_row($avatarg);
		$this->db->free_result($avatarg);

		$avatar_gallery = array();
		if (!empty($smf_avatarg) && file_exists($smf_avatarg))
			$avatar_gallery = get_files_recursive($smf_avatarg);

		foreach ($avatar_gallery as $file)
		{
			$file = str_replace('\\', '/', $file);
			$rows[] = array(
				'id_member' => 0,
				'basedir' => $smf_avatarg,
				'full_path' => dirname($file),
				'filename' => basename($file),
			);
		}

		$avatarg = $this->db->query("
			SELECT value
			FROM {$this->config->from_prefix}settings
			WHERE variable = 'custom_avatar_dir';");
		list ($smf_avatarg) = $this->db->fetch_row($avatarg);
		$this->db->free_result($avatarg);

		$avatar_custom = array();
		if (!empty($smf_avatarg) && file_exists($smf_avatarg))
			$avatar_custom = get_files_recursive($smf_avatarg);

		foreach ($avatar_custom as $file)
		{
			$file = str_replace('\\', '/', $file);
			preg_match('~avatar_(\d+)_\d+~i', $file, $match);
			if (!empty($match[1]))
				$id_member = $match[1];
			else
				$id_member = 0;

			$rows[] = array(
				'id_member' => $id_member,
				'basedir' => $smf_avatarg,
				'full_path' => dirname($file),
				'filename' => basename($file),
			);
		}

		return $rows;
	}

	public function codeCopysmiley()
	{
		$request = $this->db->query("
			SELECT value
			FROM {$this->config->from_prefix}settings
			WHERE variable = 'smileys_dir';");
		list ($smf_smileys_dir) = $this->db->fetch_row($request);

		if (!empty($smf_smileys_dir) && file_exists($smf_smileys_dir))
		{
			$smf_smileys_dir = str_replace('\\', '/', $smf_smileys_dir);
			$smiley = array();
			$files = get_files_recursive($smf_smileys_dir);
			foreach ($files as $file)
			{
				$file = str_replace('\\', '/', $file);
				$smiley[] = array(
					'basedir' => $smf_smileys_dir,
					'full_path' => dirname($file),
					'filename' => basename($file),
				);
			}
			if (!empty($smiley))
				return array($smiley);
			else
				return false;
		}
		else
			return false;
	}
}