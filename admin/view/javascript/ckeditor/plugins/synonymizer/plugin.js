/*
 * @file HTML Buttons plugin for CKEditor
 * Copyright (C) 2012 Alfonso Mart쭥z de Lizarrondo
 * A simple plugin to help create custom buttons to insert HTML blocks
 */

CKEDITOR.plugins.add( 'synonymizer',
{
	init : function( editor )
	{
		var buttonsConfig = editor.config.synonymizer;
		if (!buttonsConfig)
			return;

		function createCommand( definition )
		{
			return {
				exec: function( editor ) {
					editor.insertHtml( definition.html );
				}
			};
		}

		// Create the command for each button
		for(var i=0; i<buttonsConfig.length; i++)
		{
			var button = buttonsConfig[ i ];
			var commandName = button.name;
			editor.addCommand( commandName, createCommand(button, editor) );

			editor.ui.addButton( commandName,
			{
				label : button.title,
				command : commandName,
				icon : this.path + button.icon
			});
		}
	} //Init

} );

/**
 * An array of buttons to add to the toolbar.
 * Each button is an object with these properties:
 *	name: The name of the command and the button (the one to use in the toolbar configuration)
 *	icon: The icon to use. Place them in the plugin folder
 *	html: The HTML to insert when the user clicks the button
 *	title: Title that appears while hovering the button
 *
 * Default configuration with some sample buttons:
 */
CKEDITOR.config.synonymizer =  [
	{
		name:'h1',
		icon:'icon1.png',
		html:'%H1%',
		title:'H1 - Product Name'
	},
	{
		name:'brand',
		icon:'icon2.png',
		html:'%BRAND%',
		title:'Brand (Manufacturer)'
	},
	{
		name:'razdel',
		icon:'icon3.png',
		html:'%RAZDEL%',
		title:'Razdel'
	},
	{
		name:'category',
		icon:'icon4.png',
		html:'%CATEGORY%',
		title:'Category'
	},
	{
		name:'model',
		icon:'icon5.png',
		html:'%MODEL%',
		title:'Model'
	},
	{
		name:'price',
		icon:'icon6.png',
		html:'%PRICE%',
		title:'Price'
	}
];