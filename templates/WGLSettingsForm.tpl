{**
 * plugins/generic/WGL/templates/WGLSettingsForm.tpl
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Usage statistics plugin management form.
 *
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#WGLSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="WGLSettingsForm" method="post" action="{url op="manage" category="oaiMetadataFormats" plugin=$pluginName verb="save"}">
	{csrf}

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="WGLSettingsFormNotification"}

	{fbvFormArea id="WGLDisplayOptions" title="plugins.generic.wgl.settings.title"}

		{fbvFormSection for="settingsDescription" description="plugins.OAIMetadata.wgl.settings.description"}
		{fbvElement type="text" id="wglSettings" value=$wglSettings size=$fbvStyles.size.SMALL}

		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="WGLSettingsFormSubmit" submitText="common.save" hideCancel=true}
</form>
