// Alias Select2 in case we need to delete it
jQuery.fn.wickedFoldersSelect2 = jQuery.fn.select2;

if ( wickedFoldersSettings.isElementorActive ) {
	// Elementor uses a different version of select2. Delete the version that
	// was added by Wicked Folders (Elementor loads Select2 later so it will
	// restore it then)
	delete jQuery.fn.select2;
}

(function($){
	$(function(){

		var View = wickedfolders.views.View,
			FolderTree = wickedfolders.views.FolderTree,
			AttachmentFilters = wp.media.view.AttachmentFilters;

		var WickedFoldersAttachmentFilter = AttachmentFilters.extend({

			id: 'wicked-folders-attachment-filter',

			createFilters: function() {

				var filters = {};

				_.each( WickedFoldersProData.folders || {}, function( value, index ) {

					var space = '&nbsp;&nbsp;&nbsp;';

					filters[ index ] = {
						text: 	space.repeat( value.depth ) + value.name,
						props: 	{
							wf_attachment_folders: value.id,
							wicked_folder_type: value.type
						}
					};

				});

				filters.all = {
					text:  wickedFoldersL10n.allFolders,
					props: {
						wf_attachment_folders: ''
					},
					priority: 10
				};

				this.filters = filters;

			}

		});

		wickedfolders.views.AttachmentsBrowserItemsMovedFeedback = wickedfolders.views.View.extend({
			className: 'wicked-items-moved-feedback',

			render: function(){
				// TODO: l10n
				this.$el.html( 'Item(s) moved' );
				return this;
			}
		});

		wickedfolders.views.AttachmentsBrowserDragDetails = wickedfolders.views.View.extend({
			className: 'wicked-drag-details',
			template: _.template( $( '#tmpl-wicked-attachment-browser-drag-details' ).html(), wickedfolders.util.templateSettings ),

			initialize: function(){
				this.collection.on( 'add remove reset', this.render, this );

				_.defaults( this.options, {
					enableCopy: true
				} );
			},

			render: function(){
				this.$el.html( this.template({
					count: 		this.collection.length,
					enableCopy: this.options.enableCopy
				}) );
				return this;
			}
		});

		wickedfolders.views.UploaderFolderSelect = wickedfolders.views.FolderSelect.extend({
			events: {
				'change': 'changed'
			},

			initialize: function( options ){
				wickedfolders.views.FolderSelect.prototype.initialize.apply( this, arguments );

				this.options.controller.state().on( 'change:wickedSelectedFolder', function( folder ){
					this.setSelectedFolder();
				}, this );

				this.options.controller.on( 'uploader:ready', function(){
					this.setSelectedFolder();
				}, this );
			},

			changed: function(){
				wickedfolders.views.FolderSelect.prototype.changed.apply( this, arguments );
				var id = this.$el.val();
				this.options.controller.uploader.uploader.param( 'wicked_folder_id', id );
			},

			setSelectedFolder: function(){
				var folder = this.options.controller.state().get( 'wickedSelectedFolder' );
				// Make sure the folder exists as an option before trying
				// to change it (for example, dynamic folders aren't part of
				// the dropdown). Also, only change the folder if syncing
				// is enabled
				if ( folder && this.$( '[value="' + folder.id + '"]' ).length && this.options.syncUploadFolderDropdown ) {
					this.options.selected = folder.id;
					this.$el.val( folder.id );
					this.options.controller.uploader.uploader.param( 'wicked_folder_id', folder.id );
				}
			}
		});

		wickedfolders.views.AttachmentsBrowserFolderTreeToggle = wickedfolders.views.View.extend({
			tagName: 	'a',
			className: 	'wicked-toggle-folders',
			attributes: {
				href: 	'#',
				title: 	wickedFoldersL10n.toggleFolders
			},
			events: {
				'click': 'click'
			},
			/*
			initialize: function(){
				var state = this.options.controller.state(),
					preferenceKey = this.options.controller.options.modal ? 'wickedFoldersPaneExpandedModal' : 'wickedFoldersPaneExpandedGrid',
					expandedPreference = window.getUserSetting( preferenceKey, true ),
					expandedPreference = 'false' == expandedPreference ? false : true;

				state.on( 'change:wickedFoldersPaneExpanded', function( state ){

					window.setUserSetting( preferenceKey, state.get( 'wickedFoldersPaneExpanded' ) );
					this.render();
					$( window ).trigger( 'resize.media-modal-columns' );
				}, this );

				if ( ! state.has( 'wickedFoldersPaneExpanded' ) ) {
					state.set( 'wickedFoldersPaneExpanded', expandedPreference );
				}

			},
			*/
			click: function( e ){
				e.preventDefault();
				//var expanded = this.options.controller.state().get( 'wickedFoldersPaneExpanded' );
				//this.options.controller.state().set( 'wickedFoldersPaneExpanded', ! expanded );
				var expanded = this.model.get( 'isFolderPaneVisible' );
				this.model.set( 'isFolderPaneVisible', ! expanded );
			},
			/*
			render: function(){
				var expanded = this.options.controller.state().get( 'wickedFoldersPaneExpanded' );
				if ( expanded ) {
					this.options.browser.$el.addClass( 'wicked-folder-pane-expanded' );
				} else {
					this.options.browser.$el.removeClass( 'wicked-folder-pane-expanded' );
				}
				return this;
			}
			*/
		});

		wickedfolders.views.AttachmentFolders = wp.media.view.Attachment.extend({
	        tagName:    'div',
			className: 	'wicked-attachment-folders',
			events: {
			},

	        initialize: function(){
				this.options = _.defaults( this.options, {
					wickedFolders: new wickedfolders.collections.Folders(),
					showItemCount: false
				});

				this.folderTree = new wickedfolders.views.FolderTree({
					collection: 	this.options.wickedFolders,
					showCheckboxes: true,
					showItemCount: 	this.options.showItemCount,
					expandAll:		true,
					model: 			new wickedfolders.models.FolderTreeState({
						checked: 	this.model.get( 'wickedFolders' )
					})
				});

				this.folderTree.model.on( 'change:checked', this.update, this );

				this.$el.append( '<h2>' + wickedFoldersL10n.folders + '</h2>' );
				this.$el.append( '<div class="wicked-container">' );

				wp.media.view.Attachment.prototype.initialize.apply( this, arguments );

				// TODO: look into a better way of handling this...for now, this
				// is a crude way of getting a newly uploaded attachment to show
				// up in the selected folder
				this.model.on( 'change:nonces', function( model ){
					this.options.browser.filterAttachments();
				}, this );

				this.options.wickedFolders.on( 'add remove change:parent', this.render, this );
	        },

	        render: function(){
				// See wp.media.view.Attachment.render for read only logic
				var options = _.defaults( this.model.toJSON(), {
					readOnly: 	false,
					can: 		{}
				}, this.options );

				if ( options.nonces ) {
					options.can.save = !! options.nonces.update;
				}

				if ( options.controller.state().get('allowLocalEdits') ) {
					options.allowLocalEdits = true;
				}

				options.readOnly = options.can.save || options.allowLocalEdits ? false : true;

				this.folderTree.model.set( 'checked', this.model.get( 'wickedFolders' ), { silent: true } );
				this.folderTree.model.set( 'readOnly', options.readOnly, { silent: true } );

				this.folderTree.render();

				this.folderTree.$( 'li' ).addClass( 'wicked-expanded' );
				this.$( '.wicked-container' ).html( this.folderTree.el );
				this.folderTree.delegateEvents();

				if ( this.options.wickedFolders.children( '0' ).length ) {
					this.$el.show();
				} else {
					this.$el.hide();
				}
	        },

			update: function(){
				var view = this;
				var checked = _.map( this.$( '[type="checkbox"]:checked' ), function( item ){
					return $( item ).val();
				});

				if ( ! checked.length ) checked = false;

				// The base view already re-renders when a checkbox is changed
				// so set this silently to prevent the view from rending another
				// time
				this.model.set( 'wickedFolders', checked, { silent: true } );
				this.model.save( null, {
					success: function(){
						view.options.wickedFolders.fetch();
					}
				});

				this.options.browser.filterAttachments();
			},

			remove: function(){
				this.folderTree.remove();
				wp.media.view.Attachment.prototype.remove.apply( this, arguments );
			},

		});

		wickedfolders.views.AttachmentsBrowserFolderTree = FolderTree.extend({
			className: 'wicked-folder-tree',

			initialize: function( options ) {
				FolderTree.prototype.initialize.apply( this, arguments );

				this.model.on( 'change:selected', function( state ){
					var selected = state.get( 'selected' ),
						folder = this.collection.get( selected ),
						state = this.options.controller.state(),
						acfField = _.get( state, ['frame', 'acf', 'data', 'field'], false );

					// TODO: fix folder tree state model so a selected folder always
					// exists
					if ( _.isUndefined( folder ) ) folder = new wickedfolders.models.Folder({
						id: '0',
					 	type: 'Wicked_Folders_Folder'
					});

					this.options.controller.state().set( 'wickedSelectedFolder', folder );

					// Persist ACF field, otherwise ACF will apply
					// clear_acf_errors_for_core_requests on the backend to
					// the request and ACF errors (such as size restrictions)
					// won't be persisted. Set silently to prevent triggering an
					// additional query
					if ( acfField ) {
						this.options.props.set( '_acfuploader', acfField, { silent: true } );
					}

					if ( '0' == folder.id ) {
						this.options.props.unset( 'wicked_folder_type', { silent: true } );
						this.options.props.unset( 'wf_attachment_folders' );
					} else {
						// Prevent attachments from being queried (and filtered)
						// when on Document Gallery plugin's edit gallery screen
						if ( 'dg-edit' != this.options.controller.state().id ) {
							// An AJAX request gets triggered anytime a property is changed
							// so set the first property silently
							this.options.props.set( 'wicked_folder_type', folder.get( 'type' ), { silent: true } );
							this.options.props.set( 'wf_attachment_folders', selected );
						}
					}

					// Deactivate bulk select mode
					if ( ! this.options.controller.options.modal ) {
						this.options.controller.deactivateMode( 'select' ).activateMode( 'edit' );
					}

					this.expandToSelected();

				}, this );

				this.model.on( 'change:expanded', function( state ){
					var expanded = state.get( 'expanded' );

					this.options.controller.state().set( 'wickedExpandedFolders', expanded );
				}, this );

				this.options.props.on( 'change:wf_attachment_folders', function( props ){
					var selected = props.get( 'wf_attachment_folders' ) || '0';
					this.model.set( 'selected', selected );
				}, this );

				this.collection.on( 'add remove change:parent', this.render, this );
			},

			render: function(){
				// var selected = this.options.controller.state().get( 'wickedSelectedFolder' ),
				// 	expanded = this.options.controller.state().get( 'wickedExpandedFolders' );
				//
				// if ( ! selected ) selected = { id: '0' };
				// if ( ! expanded ) expanded = [ '0' ];
				//
				// this.model.set( 'selected', selected.id );
				// this.model.set( 'expanded', expanded );

				wickedfolders.views.FolderTree.prototype.render.call( this );

				this.initDragDrop();

				return this;

			},

			initDragDrop: function(){
				var view = this,
					folderPaneController = this.options.folderPaneController,
					mode = folderPaneController.get( 'organizationMode' );

				if ( 'organize' == mode ) {
					this.$( '.wicked-folder-leaf.wicked-movable' ).not( '[data-folder-id="0"]' ).draggable( {
						revert: 'invalid',
						helper: 'clone',
						start: function(){
							var folderId = $( this ).attr( 'data-folder-id' ),
	                            folder = view.collection.get( folderId ),
	                            parentId = folder.get( 'parent' );

							view.$( '.wicked-tree' ).addClass( 'highlight-editable' );

							if ( 0 != parentId ) {
								view.$( '[data-folder-id="0"]' ).addClass( 'editable' );
							}
						},
						stop: function( e, ui ){
	                        view.$( '.wicked-tree' ).removeClass( 'highlight-editable' );
	                        view.$( '[data-folder-id="0"]' ).removeClass( 'editable' );
	                    }
					} );
				}

				this.$( '.wicked-tree [data-folder-id="0"] .wicked-folder, .wicked-tree [data-folder-id="unassigned_dynamic_folder"] .wicked-folder' ).droppable( {
					hoverClass: 'wicked-drop-hover',
					accept: function( draggable ){

						// TODO: prevent attachments from being moved into folder they're already in

						var destinationFolderId = $( this ).parents( 'li' ).eq( 0 ).attr( 'data-folder-id' ),
							destinationFolder = view.collection.get( destinationFolderId ),
							draggedFolderId = draggable.attr( 'data-folder-id' ),
							draggedFolder = view.collection.get( draggedFolderId ),
							folder = view.collection.get( view.model.get( 'selected' ) ),
							accept = false;

						if ( draggable.hasClass( 'wicked-folder' ) || draggable.hasClass( 'wicked-folder-leaf' ) || draggable.hasClass( 'attachment' ) ) {
							accept = true;
						}

						if ( draggable.hasClass( 'wicked-folder-leaf' ) ) {
							var parent = draggable.parents( 'li' ).eq( 0 ).attr( 'data-folder-id' );
							// Don't allow folders to be moved to the folder they're already in
							if ( destinationFolderId == parent ) {
								accept = false;
							}

							// Don't allow folders to be dragged to the 'Unassigned' dynamic folder
							if ( destinationFolderId == 'unassigned_dynamic_folder' ) {
								accept = false;
							}

							// Don't allow folders to be dropped when not in organize mode
							if ( 'organize' != mode ) {
								accept = false;
							}

							// Don't allow dropping if either folder isn't editable
							if ( '0' != destinationFolderId ) {
								if ( ! destinationFolder.get( 'editable' ) || ! draggedFolder.get( 'editable' ) ) {
									accept = false;
								}
							}
						}

						if ( draggable.hasClass( 'attachment' ) ) {
							// For now, prevent attachments from being dragged to the
							// root folder
							if ( '0' == destinationFolderId ) {
								accept = false;
							}

							// Prevent attachments from being drug into folder they're
							// already in
							if ( destinationFolderId == folder.id ) {
								accept = false;
							}

							// Make sure target folder allows assignment
							if ( ! destinationFolder.get( 'assignable' ) ) {
								accept = false;
							}
						}

						return accept;
					},
					tolerance: 'pointer',
					drop: function( e, ui ) {

						// TODO: clean this up

						var destinationFolderId = $( this ).parents( 'li' ).eq( 0 ).attr( 'data-folder-id' ),
							allAttachments = wp.media.model.Attachments.all,
							currentFolderId = view.model.get( 'selected' ),
							currentFolder = view.collection.get( currentFolderId );

						if ( ui.helper.hasClass( 'wicked-drag-details' ) ) {
							var attachments = view.options.dragSelection,
								assignable = currentFolder.get( 'assignable' ),
								copy = e.shiftKey || ! assignable,
								unassign = !! ( destinationFolderId == 0 || destinationFolderId == 'unassigned_dynamic_folder' );

							if ( unassign && ! confirm( wickedFoldersL10n.confirmUnassign ) ) {
								return false;
							}

							// Loop through attachments being moved
							attachments.each( function( attachment ){
								if ( unassign ) {
									attachment.set( 'wickedFolders', [] );
								} else {
									// Get the attachent's current folders
									var folders = _.clone( attachment.get( 'wickedFolders' ) );
									// If we're not copying the attachment, remove
									// the attachment from the current folder
									if ( ! copy ) folders = _.without( folders, currentFolderId );
									// Add the destination folder
									folders = _.union( folders, [ destinationFolderId ] );
									// Update the attachment's folders
									attachment.set( 'wickedFolders', folders );
								}
							});

							view.options.browser.flashItemsMovedFeedback();

							// Update the attachment browser display
							view.options.browser.filterAttachments();

							// Clear the selection after dropping to prevent the
							// same selection from inadvertently being dragged
							// again
							view.options.browser.options.selection.reset();

							if ( unassign ) {
								$.ajax(
									wickedFoldersSettings.ajaxURL,
									{
										data: {
											'action':       'wicked_folders_unassign_folders',
											'object_id':    attachments.pluck( 'id' ),
											'taxonomy':     currentFolder.get( 'taxonomy' )
										},
										method: 'POST',
										dataType: 'json',
										success: function( data ) {
											updateItemCounts( data.folders );

											notifications.add( new Notification( {
												title: 	'Success',
												message: 'Unassigned ' + ( 1 == attachments.length ? 'item' : 'items' ) + ' from folders.'
											} ) );
										},
										error: function( data ) {
										}
									}
								);
							} else {
								// Move or copy the selected attachments
								// TODO: error handling?
								$.ajax(
									wickedFoldersSettings.ajaxURL,
									{
										data: {
											// TODO: add nonce
											'action':                   'wicked_folders_move_object',
											'object_type':              'post',
											'object_id':                attachments.pluck( 'id' ),
											'destination_object_id':    destinationFolderId,
											'post_type':				'attachment',
											// Omitting the source folder will result in a copy on the back-end
											'source_folder_id':         copy ? false : currentFolderId
										},
										method: 'POST',
										dataType: 'json',
										success: function( data ) {
											var message = ( copy ? 'Copied' : 'Moved' ) + ' ' + ( 1 == attachments.length ? 'item' : attachments.length + ' items' ) + ' to folder.';

											updateItemCounts( data.folders );

											notifications.add( new Notification( {
												title: 	'Success',
												message: message,
											} ) );
										},
										error: function( data ) {
										}
									}
								);
							}
						} else {

							var objectId = $( ui.draggable ).attr( 'data-folder-id' ),
								folder = view.collection.get( objectId );

							folder.set( 'parent', destinationFolderId );
							folder.save();

						}

						function updateItemCounts( folders ) {
							_.each( folders, function( folder ){
								var _folder = view.collection.get( folder.id );

								if ( 'undefined' != typeof _folder ) {
									_folder.set( 'itemCount', folder.itemCount );
								}
							});
						}
					}
				});

				if ( 'sort' == mode ) {
					this.$( '.wicked-tree [data-folder-id="0"] ul' ).sortable({
						items: '> li',
						stop: function( e, ui ){
							var items = ui.item.parent().find( '> li' )
								folders = view.collection,
								changedFolders = new wickedfolders.collections.Folders();

								var controller = new wickedfolders.models.FolderBrowserController();

							items.each( function( index, item ){
								var folderId = $( item ).attr( 'data-folder-id' ),
									folder = folders.get( folderId );

								folder.set( 'order', index );

								changedFolders.add( folder );
							});

							folderPaneController.set( 'sortMode', 'custom' )

							changedFolders.saveOrder();

							view.collection.sort();
						}
					});
				}
			}
		});

		wickedfolders.views.AttachmentsBrowserFolderPane = View.extend({
			id: 		'wicked-object-folder-pane',
			className: 	'wicked-folder-pane',
			template: 	_.template( $( '#tmpl-wicked-attachment-browser-folder-pane' ).html(), wickedfolders.util.templateSettings ),

			events: {
				'keyup [name="wicked_folder_search"]': 'search',
			},

			initialize: function(){
				var view = this;

				this.$el.html( this.template() );

				this.toolbar = new wickedfolders.views.AttachmentsBrowserFolderPaneToolbar({
					parent:		this,
					collection:	this.collection,
					state:		this.options.state,
					pane:		this
				});

				this.folderTree = new wickedfolders.views.AttachmentsBrowserFolderTree({
					collection: 			this.collection,
					model:					this.options.state,
					controller: 			this.options.controller,
					props: 					this.options.props,
					browser:				this.options.browser,
					dragSelection: 			this.options.dragSelection,
					folderPaneController: 	this.model,
					showItemCount:			this.model.get( 'showItemCount' )
				});

				this.createFolderDetails();

				this.makeResizable();

				this.$( '.wicked-toolbar-container' ).append( this.toolbar.render().el );
				this.$( '.wicked-folder-tree-container' ).append( this.folderTree.render().el );

				this.options.state.on( 'change:selected', this.folderChanged, this );

				this.model.on( 'change:isFolderPaneVisible', this.initWidth, this );
				this.model.on( 'change:organizationMode', function(){
					this.folderTree.render();
				}, this );
				this.model.on( 'change:sortMode', function(){
					var mode = this.model.get( 'sortMode' );

					this.collection.sortMode = mode;
					this.collection.sort();
					this.folderTree.render();
				}, this );

				this.initWidth();

				if ( this.options.controller.options.modal ) {
					this.$el.addClass( 'wicked-media-modal-folder-pane' );
				}

				if ( this.options.enableHorizontalScrolling ) {
					this.$el.addClass( 'wicked-scroll-horizontal' );
				}

				this.updateSearch = _.debounce( function( search ){
					view.options.state.set( 'search', search );
				}, 750 );
			},

			makeResizable: function() {
	            var view = this;

	            this.$( '.wicked-resizer' ).resizable( {
	                resizeHeight:   false,
	                handles:        'e',
	                minWidth:       this.options.controller.options.modal ? 221 : 150,
	                containment:    $( '#wpcontent' ),
	                resize:         function( e, ui ) {
	                    view.setWidth( ui.size.width );
	                },
	                stop:           function( e, ui ) {
	                    view.model.set( 'treePaneWidth', ui.size.width );
	                }
	            } );

	            if ( wickedfolders.util.isRtl() ) {
	                // Risizing to left is not working...disable for now
	                //this.$( '.wicked-resizer' ).resizable( 'option', 'handles', 'w' );
	                this.$( '.wicked-resizer' ).resizable( 'disable' );
	            }
	        },

			initWidth: function(){
				var visible = this.model.get( 'isFolderPaneVisible' ),
					width = visible ? this.model.get( 'treePaneWidth' ) : 0;

				this.setWidth( width );
			},

			setWidth: function( width ){
				if ( this.options.controller.options.modal ) {
					this.$el.css( 'width', width - 6 + 'px' );
					this.$( '.wicked-resizer' ).css( 'width', width + 'px' );

					if ( wickedfolders.util.isRtl() ) {
						this.options.browser.toolbar.$el.css( 'left', '' );
						this.options.browser.attachments.$el.css( 'left', '' );
						this.options.browser.toolbar.$el.css( 'right', width + 'px' );
						this.options.browser.attachments.$el.css( 'right', width + 'px' );

						// attachmentsWrapper was introduced in WordPress 5.8
						if (this.options.browser.attachmentsWrapper) {
							this.options.browser.attachmentsWrapper.$el.css( 'left', '' );
							this.options.browser.attachmentsWrapper.$el.css( 'right', width + 'px' );
						}

						// This should be moved to the folder toggle view
						this.options.browser.wickedFoldersPaneToggle.$el.css( 'left', 'auto' );

						if ( 0 == width ) {
							this.options.browser.wickedFoldersPaneToggle.$el.css( 'right', '4px' );
						} else {
							this.options.browser.wickedFoldersPaneToggle.$el.css( 'right', width - 19 + 'px' );
						}
					} else {
						this.options.browser.toolbar.$el.css( 'left', width + 'px' );
						this.options.browser.attachments.$el.css( 'left', width + 'px' );
						this.options.browser.toolbar.$el.css( 'right', '' );
						this.options.browser.attachments.$el.css( 'right', '' );

						if (this.options.browser.attachmentsWrapper) {
							this.options.browser.attachmentsWrapper.$el.css( 'left', width + 'px' );
							this.options.browser.attachmentsWrapper.$el.css( 'right', '' );
						}

						// This should be moved to the folder toggle view
						this.options.browser.wickedFoldersPaneToggle.$el.css( 'right', 'auto' );

						if ( 0 == width ) {
							this.options.browser.wickedFoldersPaneToggle.$el.css( 'left', '4px' );
						} else {
							this.options.browser.wickedFoldersPaneToggle.$el.css( 'left', width - 19 + 'px' );
						}
					}

				} else {
					this.$( '.wicked-content' ).css( 'width', width - 12 + 'px' );
					this.$( '.wicked-resizer' ).css( 'width', width + 'px' );

					if ( $( 'body' ).hasClass( 'rtl' ) ) {
						$( '#wpcontent' ).css( 'paddingRight', width + 11 + 'px' );
						$( '#wpfooter' ).css( 'right', width - 6 + 'px' );
					} else {
						$( '#wpcontent' ).css( 'paddingLeft', width + 11 + 'px' );
						$( '#wpfooter' ).css( 'left', width - 6 + 'px' );
					}
				}
			},

			folderChanged: function(){
				// TODO: figure out why 'selected' points to deleted folder in some instances
				// Example: Open modal, switch to Create Gallery, add folder,
				// switch to dynamic folder, switch back to newly created folder,
				// delete folder...selected is still pointing to deleted folder
				var id = this.options.state.get( 'selected' )
					folder = this.collection.get( id ),
					mode = this.folderDetails.options.mode;

				if ( ! _.isUndefined( folder ) && 'Wicked_Folders_Term_Folder' == folder.get( 'type' ) ) {
					// Only regenerate the view if we're not in add mode
					if ( 'edit' == mode ) {
						this.createFolderDetails({
							mode: mode
						});
					}
				} else {
					this.folderDetails.$el.hide();
				}
			},

			addFolder: function(){
				// TODO: disable add folder button instead while in add mode
				// Don't do anything if the folder details view is already in
				// add mode and visible
				if ( 'add' == this.folderDetails.options.mode && this.folderDetails.$el.is( ':visible' ) ) return;

				var id = this.options.state.get( 'selected' ),
					folder = this.collection.get( id ),
					parent = '0';

				if ( 'Wicked_Folders_Term_Folder' == folder.get( 'type' ) ) {
					parent = folder.id;
				}

				this.createFolderDetails({
					mode: 	'add',
					model: 	new wickedfolders.models.Folder({
						postType: 		'attachment',
						taxonomy: 		'wf_attachment_folders',
						showItemCount: 	this.model.get( 'showItemCount' ),
						parent: 		parent
					})
				});

				this.folderDetails.$el.show();
				this.folderDetails.$( '[name="wicked_folder_name"]' ).get( 0 ).focus();

				if ( ! _.isUndefined( this.folderPaneSettings ) ) {
	                this.folderPaneSettings.$el.hide();
	            }
			},

			editFolder: function(){

				this.createFolderDetails({
					mode: 'edit'
				});

				this.folderDetails.$el.show();
				this.folderDetails.$( '[name="wicked_folder_name"]' ).get( 0 ).focus();

				if ( ! _.isUndefined( this.folderPaneSettings ) ) {
					this.folderPaneSettings.$el.hide();
				}
			},

			deleteFolder: function(){
				this.createFolderDetails({
					mode: 'delete'
				});
				this.folderDetails.$el.show();

				if ( ! _.isUndefined( this.folderPaneSettings ) ) {
					this.folderPaneSettings.$el.hide();
				}
			},

			editPaneSettings: function(){
	            var visible = false;

	            if ( ! _.isUndefined( this.folderPaneSettings ) ) {
	                visible = this.folderPaneSettings.$el.is( ':visible' );
	                this.folderPaneSettings.remove();
	            }

	            this.folderPaneSettings = new wickedfolders.views.FolderPaneSettings({
	                pane: this
	            });

	            this.$( '.wicked-folder-pane-settings-container' ).append( this.folderPaneSettings.render().el );

	            //if ( ! visible ) this.folderPaneSettings.$el.hide();
	            if ( ! _.isUndefined( this.folderDetails ) ) {
	                this.folderDetails.$el.hide();
	            }
	        },

			expandAll: function(){
				var ids = this.collection.pluck( 'id' );
				this.folderTree.model.set( 'expanded', ids );
			},

			collapseAll: function(){
				this.folderTree.model.set( 'expanded', [ '0' ] );
			},

			createFolderDetails: function( args ){

				var id = this.options.state.get( 'selected' ),
					folder = this.collection.get( id )
					args = args || {},
					visible = false;

				_.defaults( args, {
					controller: this.options.controller,
					collection:	this.collection,
					state:		this.options.state,
					model:		folder
				} );

				if ( ! _.isUndefined( this.folderDetails ) ) {
					visible = this.folderDetails.$el.is( ':visible' );
					this.folderDetails.remove();
				}

				this.folderDetails = new wickedfolders.views.AttachmentsBrowserFolderDetails( args );

				this.$( '.wicked-folder-details-container' ).append( this.folderDetails.render().el );

				if ( ! visible ) this.folderDetails.$el.hide();

			},

			search: function( e ){
				var search = $( e.currentTarget ).val().trim();

				this.updateSearch( search );
			}
		});

		wickedfolders.views.AttachmentsBrowserFolderPaneToolbar = View.extend({
			tagName: 	'ul',
			className: 	'wicked-folder-pane-toolbar',
			events: {
				'click a': 						'clickLink',
				'click .wicked-add-folder': 	'addFolder',
				'click .wicked-edit-folder': 	'editFolder',
				'click .wicked-delete-folder': 	'deleteFolder',
				'click .wicked-toggle-all': 	'toggleAll',
				'click .wicked-pane-settings': 	'editSettings',
			},

			initialize: function(){

				var l10n = wickedFoldersL10n;

				this.$el.append( '<li><a class="wicked-add-folder" href="#" title="' + l10n.addNewFolderLink + '"><span class="screen-reader-text">' + l10n.addNewFolderLink + '</span></a></li>' );
				this.$el.append( '<li><a class="wicked-edit-folder" href="#" title="' + l10n.editFolderLink + '"><span class="screen-reader-text">' + l10n.editFolderLink + '</span></a></li>' );
				this.$el.append( '<li><a class="wicked-delete-folder" href="#" title="' + l10n.deleteFolderLink + '"><span class="screen-reader-text">' + l10n.deleteFolderLink + '</span></a></li>' );
				this.$el.append( '<li><a class="wicked-toggle-all wicked-expand-all" href="#" title="' + l10n.expandAllFoldersLink + '"><span class="screen-reader-text">' + l10n.expandAllFoldersLink + '</span></a></li>' );
				this.$el.append( '<li><a class="wicked-pane-settings" href="#" title="' + l10n.settings + '"><span class="screen-reader-text">' + l10n.settings + '</span></a></li>' );

				this.options.state.on( 'change:selected', this.onFolderChanged, this );

				this.onFolderChanged();

			},

			clickLink: function( e ){
				e.preventDefault();
			},

			addFolder: function(){
				this.options.parent.addFolder();
			},

			editFolder: function( e ){
				if ( $( e.currentTarget ).hasClass( 'wicked-disabled' ) ) return;
				this.options.parent.editFolder();
			},

			deleteFolder: function( e ){
				if ( $( e.currentTarget ).hasClass( 'wicked-disabled' ) ) return;
				this.options.parent.deleteFolder();
			},

			editSettings: function() {
	            this.options.parent.editPaneSettings();
	        },

			expandAll: function(){
				this.options.parent.expandAll();
			},

			collapseAll: function(){
				this.options.parent.collapseAll();
			},

			toggleAll: function( e ){
	            var $el = $( e.currentTarget ),
	                l10n = wickedFoldersL10n;

	            if ( $el.hasClass( 'wicked-expand-all' ) ) {
	                $el.removeClass( 'wicked-expand-all' );
	                $el.addClass( 'wicked-collapse-all' );
	                $el.attr( 'title', l10n.collapseAllFoldersLink );
	                $el.find( '.screen-reader-text' ).text( l10n.collapseAllFoldersLink );

	                this.expandAll();
	            } else {
	                $el.addClass( 'wicked-expand-all' );
	                $el.removeClass( 'wicked-collapse-all' );
	                $el.attr( 'title', l10n.expandAllFoldersLink );
	                $el.find( '.screen-reader-text' ).text( l10n.expandAllFoldersLink );

	                this.collapseAll();
	            }
	        },

			onFolderChanged: function(){

				var id = this.options.state.get( 'selected' ),
					folder = this.collection.get( id );

				// TODO: fix folder tree state model so a selected folder always
				// exists
				if ( _.isUndefined( folder ) ) folder = new wickedfolders.models.Folder();

				this.$( 'a' ).removeClass( 'wicked-disabled' );

				if ( 'Wicked_Folders_Term_Folder' != folder.get( 'type' ) ) {
					this.$( '.wicked-edit-folder' ).addClass( 'wicked-disabled' );
					this.$( '.wicked-delete-folder' ).addClass( 'wicked-disabled' );
				}

				if ( ! this.options.pane.model.get( 'enableCreate' ) ) {
	                this.$( '.wicked-add-folder' ).addClass( 'wicked-disabled' );
	            }

	            if ( ! folder.get( 'editable' ) ) {
	                this.$( '.wicked-edit-folder' ).addClass( 'wicked-disabled' );
	            }

	            if ( ! folder.get( 'deletable' ) ) {
	                this.$( '.wicked-delete-folder' ).addClass( 'wicked-disabled' );
	            }
			}

		});

		wickedfolders.views.AttachmentsBrowserFolderDetails = View.extend({
			className: 'wicked-folder-pane-panel wicked-folder-details',
			events: {
				'keyup input': 			'keyup',
				'keydown input': 		'keydown',
				'blur input': 			'setSaveButtonState',
				'click .wicked-save': 	'save',
				'click .wicked-delete': 'delete',
				'click .wicked-cancel': 'cancel',
				'click .wicked-close': 	'cancel',
				'click .wicked-clone-folder':   'cloneFolder'
			},
			template: _.template( $( '#tmpl-wicked-attachment-browser-folder-details' ).html(), wickedfolders.util.templateSettings ),

			initialize: function(){

				_.defaults( this.options, {
					mode: 'add'
				} );

				if ( _.isUndefined( this.model ) ) {
					this.model = new wickedfolders.models.Folder({
						postType: 'attachment',
						taxonomy: 'wf_attachment_folders'
					});
				}

				this.folderSelect = new wickedfolders.views.FolderSelect({
					collection: 	this.collection,
					selected:		this.model.get( 'parent' ),
					hideUneditable: true
				});

				this.listenTo( this.model, 'change:parent', this.folderParentChanged );

			},

			remove: function(){
				this.folderSelect.remove();
				View.prototype.remove.apply(this, arguments);
			},

			render: function(){

				var mode = this.options.mode,
					title = wickedFoldersL10n.editFolderLink,
					saveButtonLabel = wickedFoldersL10n.save;

				if ( 'add' == mode ) {
	                title = wickedFoldersL10n.addNewFolderLink;
	            }

	            if ( 'delete' == mode ) {
	                title           = wickedFoldersL10n.deleteFolderLink;
	                saveButtonLabel = wickedFoldersL10n.delete;
	            }

				var html = this.template({
					mode: 						this.options.mode,
					title: 						title,
					folderName:                 this.model.get( 'name' ),
					ownerId:                    this.model.get( 'ownerId' ),
					ownerName:                  this.model.get( 'ownerName' ),
					saveButtonLabel:            saveButtonLabel,
					ownerLabel: 				wickedFoldersL10n.owner,
					cloneFolderLink: 			wickedFoldersL10n.cloneFolderLink,
					cloneFolderTooltip: 		wickedFoldersL10n.cloneFolderTooltip,
					cloneChildFolders: 			wickedFoldersL10n.cloneChildFolders,
					cloneChildFoldersTooltip: 	wickedFoldersL10n.cloneChildFoldersTooltip,
					deleteFolderConfirmation: 	wickedFoldersL10n.deleteFolderConfirmation
				});

				this.folderSelect.options.selected = this.model.get( 'parent' );

				this.$el.html( html );

				this.$( '.wicked-folder-parent' ).html( this.folderSelect.render().el );

				this.setSaveButtonState();

				this.renderFolderOwner();

				return this;
			},

			renderFolderOwner: function(){
	            var page    = 1;
	            var perPage = 25;
	            var term    = '';

	            this.$( '#wicked-folder-owner-id' ).wickedFoldersSelect2({
	                width: '100%',
					dropdownParent: '#wicked-folders-select2-dropdown',
	                ajax: {
	                    url: wickedFoldersSettings.restURL + 'wp/v2/users',
	                    dataType: 'json',
	                    cache: true,
	                    data: function( params ){
	                        if ( term != params.term ) {
	                            term = params.term;
	                            page = 1;
	                        }

	                        return {
	                            per_page: perPage,
	                            search: params.term,
	                            page: page,
								wf_include_users_without_posts: true
	                        };
	                    },
	                    transport: function( params, success, failure ){
	                        var readHeaders = function( data, textStatus, jqXHR ) {
	                            var more    = false;
	                            var total   = parseInt( jqXHR.getResponseHeader( 'X-WP-Total' ) ) || 0;
	                            var fetched = page * perPage;

	                            if ( total > fetched ) {
	                                page++;
	                                more = true;
	                            }

	                            return {
	                                results: $.map( data, function( item ){
	                                    return {
	                                        id:     item.id,
	                                        text:   item.name
	                                    }
	                                } ),
	                                pagination: {
	                                    more: more
	                                }
	                            };
	                        };

	                        var request = $.ajax( params );
	                        request.then( readHeaders ).then( success );
	                        request.fail( failure );
	                    }
	                }
	            });
	        },

			keyup: function( e ){
				// Escape button
				if ( 27 == e.which ) this.$el.hide();
				this.setSaveButtonState();
			},

			keydown: function( e ) {
				// Enter key
				if ( 13 == e.which && this.$( '[name="wicked_folder_name"]' ).val().length > 0 ) {
					this.save();
				}
			},

			cancel: function( e ) {
				e.preventDefault();
				this.$el.hide();
			},

			save: function(){

				var view = this,
					parent = this.model.get( 'parent' ),
					originalFolder = this.model.clone();

				view.clearMessages();
				view.setBusy( true );

				if ( 'delete' == this.options.mode ) {
					//this.model.set( '_actionOverride', 'wicked_folders_delete_folder' );
					this.model.set( '_methodOverride', 'DELETE' );
	                this.model.destroy( {
						wait: true,
	                    success: function( model, response, options ){
							// Move the deleted folder's children to it's parent
							var children = view.collection.where( { parent: model.id } );

							if ( children.length ) {
								_.each( children, function( child ){
									// Keep silent to prevent unnecessary re-renders
									// by views monitoring the collection
									child.set( 'parent', parent, { silent: true } );
								} );
								// Trigger an event so that views monitoring the
								// collection will re-render
								view.collection.trigger( 'remove', model, {} );
							}
							view.options.state.set( 'selected', parent );
							view.setBusy( false );
							view.$el.hide();
	                    },
	                    error: function( model, response, options ){
	                        view.setErrorMessage( response.responseJSON.message );
	                        view.setSaveButtonState();
	                        view.setBusy( false );
	                    }
	                } );
	            } else {
					view.model.set( {
						name:   	this.$( '[name="wicked_folder_name"]' ).val(),
						parent: 	this.$( '[name="wicked_folder_parent"]' ).val(),
						ownerId:    this.$( '[name="wicked_folder_owner_id"]' ).val(),
						ownerName:  this.$( '[name="wicked_folder_owner_id"] option:selected' ).text()
					} );
					this.model.save( {}, {
						success: function( model, response, options ){
							if ( 'add' == view.options.mode ) {
								view.collection.add( model );
								view.model = new wickedfolders.models.Folder({
									postType: 		'attachment',
									taxonomy: 		'wf_attachment_folders',
									showItemCount: 	model.get( 'showItemCount' ),
									parent:			model.get( 'parent' )
								});
								view.render();
								// TODO: l10n
								view.flashMessage( 'Folder added.' );
								view.$( '[name="wicked_folder_name"]' ).get( 0 ).focus();
							}
							view.setSaveButtonState();
							view.setBusy( false );
							//if ( 'edit' == view.options.mode && view.options.controller.state().frame.options.modal ) {
							if ( 'edit' == view.options.mode ) {
								view.$el.hide();
							}
						},
						error: function( model, response, options ){
							var message = 'Error saving folder.';

							if ( _.has( response, 'responseJSON' ) ) {
								message = response.responseJSON.message;
							} else if ( _.has( response, 'statusText' ) ) {
								message = response.statusText;
							}

							view.setErrorMessage( message );
							view.setSaveButtonState();
							view.setBusy( false );

							// Revert model to previous values
							view.model.set( {
								name:   	originalFolder.get( 'name' ),
								parent: 	originalFolder.get( 'parent' ),
								ownerId:    originalFolder.get( 'ownerId' ),
								ownerName:  originalFolder.get( 'ownerName' )
							} );
						}
					} );
				}

			},

			setSaveButtonState: function(){

				var disabled = false;

				if ( 'delete' != this.options.mode ) {
					if ( this.$( '[name="wicked_folder_name"]' ).val().length < 1 ) {
						disabled = true;
					}
				}

				this.$( '.wicked-save' ).prop( 'disabled', disabled );

			},

			setBusy: function( isBusy ){
				if ( isBusy ) {
					this.$( '.wicked-spinner' ).css( 'display', 'inline-block' );
					this.$( '[name="wicked_folder_name"]' ).prop( 'disabled', true );
					this.$( '[name="wicked_folder_parent"]' ).prop( 'disabled', true );
					this.$( '.wicked-save' ).prop( 'disabled', true );
				} else {
					this.$( '.wicked-spinner' ).hide();
					this.$( '[name="wicked_folder_name"]' ).prop( 'disabled', false );
					this.$( '[name="wicked_folder_parent"]' ).prop( 'disabled', false );
					this.setSaveButtonState();
				}
			},

			clearMessages: function(){
				this.$( '.wicked-messages' ).removeClass( 'wicked-errors wicked-success' ).empty().hide();
			},

			setErrorMessage: function( message ){
				this.$( '.wicked-messages' ).addClass( 'wicked-errors' ).text( message ).show();
			},

			flashMessage: function( message ){
				var view = this;
				this.$( '.wicked-messages' ).addClass( 'wicked-success' ).text( message ).show();
				setTimeout( function(){
					view.$( '.wicked-messages' ).fadeOut();
				}, 1000 );
			},

			folderParentChanged: function( folder ){
				// Model change event will trigger folder select view to re-render
				// so just update the view's selected option
				this.folderSelect.options.selected = this.model.get( 'parent' );
			},

	        cloneFolder: function( e ){
				e.preventDefault();

	            var view = this,
	                options = {
	                    cloneChildren: this.$( '[name="wicked_clone_children"]' ).prop( 'checked' )
	                };

	            view.clearMessages();
	            view.setBusy( true );

	            this.model.cloneFolder( options )
	                .done( function( folders ){
	                    _.each( folders, function( folder ){
	                        view.collection.add( folder );
	                    } );

	                    view.setBusy( false );
	                    view.flashMessage( wickedFoldersL10n.cloneFolderSuccess );
	                } )
	                .fail( function( error ){
	                    view.setErrorMessage( error.responseText );
	                    view.setBusy( false );
	                });
	        }
		});

		// TODO: namespace everything

		var Folder = wickedfolders.models.Folder,
			FolderTree = wickedfolders.views.FolderTree,
			FolderTreeState = wickedfolders.models.FolderTreeState,
			FolderCollection = wickedfolders.collections.Folders,
			UploaderInline = wp.media.view.UploaderInline,
			AttachmentDetails = wp.media.view.Attachment.Details,
			Attachments = wp.media.view.Attachments,
			AttachmentsBrowser = wp.media.view.AttachmentsBrowser,
			AttachmentsBrowserDragDetails = wickedfolders.views.AttachmentsBrowserDragDetails,
			AttachmentsBrowserFolderPane = wickedfolders.views.AttachmentsBrowserFolderPane,
			AttachmentsBrowserFolderTree = wickedfolders.views.AttachmentsBrowserFolderTree,
			AttachmentsBrowserFolderTreeToggle = wickedfolders.views.AttachmentsBrowserFolderTreeToggle,
			AttachmentsBrowserItemsMovedFeedback = wickedfolders.views.AttachmentsBrowserItemsMovedFeedback,
			ObjectFolderPaneController = wickedfolders.models.ObjectFolderPaneController,
			NotificationCenter = wickedfolders.views.NotificationCenter,
			NotificationCollection = wickedfolders.collections.Notifications,
			Notification = wickedfolders.models.Notification;

		// TODO: move dragSelection out of global scope
		// Note: folder pane controllers are global so that state persists across
		// media modal instances
		var folders = new FolderCollection(),
			dragSelection = new Backbone.Collection(),
			notifications = new NotificationCollection(),
			//persistentState = new Backbone.Model({ selectedFolder: false, expandedFolders: false }),
			folderPaneController = new ObjectFolderPaneController( WickedFoldersProData.folderPaneParams ),
			modalFolderPaneController = new ObjectFolderPaneController( WickedFoldersProData.modalFolderPaneParams ),
			allFolders = folders;

		folders.taxonomy = 'wf_attachment_folders';

		_.each( WickedFoldersProData.folders, function( folder, index ) {
			folders.add( new Folder({
				id: 			folder.id,
				name: 			folder.name,
				parent: 		folder.parent,
				type: 			folder.type,
				postType: 		'attachment',
				taxonomy:		'wf_attachment_folders',
				order: 			folder.order,
				itemCount: 		folder.itemCount,
				showItemCount: 	WickedFoldersProData.showItemCount ? folder.showItemCount : false,
				editable: 		folder.editable,
				deletable: 		folder.deletable,
				assignable: 	folder.assignable,
				ownerId:		folder.ownerId,
				ownerName:		folder.ownerName
			}) );
		});

		if ( WickedFoldersProData.activeFolderId ) {
			folderPaneController.set( 'folder', folders.get( WickedFoldersProData.activeFolderId ), { silent: true } );
		}

		if ( WickedFoldersProData.modalActiveFolderId ) {
			modalFolderPaneController.set( 'folder', folders.get( WickedFoldersProData.modalActiveFolderId ), { silent: true } );
		}

		// Extend WordPress attachment browser
		wp.media.view.AttachmentsBrowser = AttachmentsBrowser.extend({

			initialize: function() {
				var attachments = this.controller.state().get( 'library' ),
					allAttachments = wp.media.model.Attachments.all;

				this.folderPaneController = this.controller.options.modal ? modalFolderPaneController : folderPaneController;

				folders.sortMode = this.folderPaneController.get( 'sortMode' );
				folders.sort();

				// Set persistent state before initialization to avoid issue
				// with change event firing too early in inline uploader before
				// uploader is fully ready
				if ( WickedFoldersProData.persistFolderState ) {
					if ( this.folderPaneController.get( 'folder' ) ) {
						this.controller.state().set( 'wickedSelectedFolder', this.folderPaneController.get( 'folder' ) );
					}
					if ( this.folderPaneController.get( 'expanded' ) ) {
						this.controller.state().set( 'wickedExpandedFolders', this.folderPaneController.get( 'expanded' ) );
					}
				}

				AttachmentsBrowser.prototype.initialize.apply( this, arguments );

				this.filterAttachmentsDebounced = _.debounce( this.filterAttachments, 100 );

				this.model.frame.on( 'open', function(){
					this.filterAttachmentsDebounced();
				}, this );

				/*
				this.model.frame.on( 'open', function(){
					var views = this.views.get();
					_.each( views, function( view ){
						if ( view.$el.hasClass( 'wicked-folder-pane') ) {
						}
					});
				}, this );
				*/
				this.createFolderPaneToggle();

				this.createFolderPane();

				this.createItemsMovedFeedback();

				this.controller.state().on( 'change:wickedSelectedFolder', function(){
					this.folderPaneController.set( 'folder', this.controller.state().get( 'wickedSelectedFolder' ) );
				}, this );

				this.controller.state().on( 'change:wickedExpandedFolders', function(){
					this.folderPaneController.set( 'expanded', this.controller.state().get( 'wickedExpandedFolders' ) );
				}, this );

				this.collection.props.on( 'change:query', this.filterAttachments, this );
				this.controller.state().get( 'library' ).on( 'reset', this.filterAttachments, this );
				this.controller.states.on( 'activate', this.filterAttachmentsDebounced, this );
				wp.media.model.Attachments.all.on( 'change:uploading', this.filterAttachmentsDebounced, this );

				// TODO: For some reason, calling filterAttachmentsDebounced
				// here is causing main 'select' button in media modal to be
				// disabled after upload
				wp.media.model.Attachments.all.on( 'add remove', this.filterAttachmentsDebounced, this );

				// Ensure the attachments that have been queried for the currently
				// active library are part of the allAttachments collection (which
				// may not include the current view's attachments yet)
				attachments.on( 'add', function( model ){
					allAttachments.add( model );
				}, this );
			},

			createFolderPane: function(){
				var folderTreeState = new FolderTreeState(),
					view = this,
					selected = this.options.controller.state().get( 'wickedSelectedFolder' ),
					expanded = this.options.controller.state().get( 'wickedExpandedFolders' ),
					stateId = this.controller.state().id;

				if ( ! selected ) selected = { id: '0' };
				if ( ! expanded ) expanded = [ '0' ];

				if ( ! this.controller.options.modal ) {
					// Don't listen to folder pane toggle event in modal
					$( 'body' ).on( 'wickedfolders:toggleFolderPane', function( e, visible ){
						view.folderPaneController.set( 'isFolderPaneVisible', visible );

						view.attachments.setColumns();
					} );
				}

				this.folderPaneController.on( 'change:isFolderPaneVisible', function( controller ){
					if ( controller.get( 'isFolderPaneVisible' ) ) {
						this.$el.addClass( 'wicked-folder-pane-expanded' );
					} else {
						this.$el.removeClass( 'wicked-folder-pane-expanded' );
					}

					$( window ).trigger( 'resize.media-modal-columns' );
				}, this );

				var folderPane = new AttachmentsBrowserFolderPane({
						collection: 				folders,
						state:						folderTreeState,
						controller: 				this.controller,
						props: 						this.collection.props,
						browser:					this,
						dragSelection: 				dragSelection,
						model:						this.folderPaneController,
						enableHorizontalScrolling: 	WickedFoldersProData.enableHorizontalScrolling
					});

				this.views.add( folderPane );

				if ( 'gallery-edit' == stateId || 'dg-edit' == stateId ) {
					this.$el.removeClass( 'wicked-folder-pane-enabled' );
					this.$el.removeClass( 'wicked-folder-pane-expanded' );

					folderPane.setWidth( 0 );
				} else {
					this.$el.addClass( 'wicked-folder-pane-enabled' );

					if ( this.folderPaneController.get( 'isFolderPaneVisible' ) ) {
						this.$el.addClass( 'wicked-folder-pane-expanded' );
					}

					folderPane.initWidth();
				}

				if ( WickedFoldersProData.persistFolderState ) {
					this.folderPaneController.on( 'change:folder', function( state ){
						folderTreeState.set( 'selected', state.get( 'folder' ).id );
					});
					this.folderPaneController.on( 'change:expandedFolders', function( state ){
						folderTreeState.set( 'expanded', state.get( 'expanded' ) );
					});
				}

				folderTreeState.set( 'expanded', expanded );
				folderTreeState.set( 'selected', selected.id );
			},

			createFolderPaneToggle: function(){
				this.wickedFoldersPaneToggle = new AttachmentsBrowserFolderTreeToggle({
					model: this.folderPaneController
				});

				this.views.add( this.wickedFoldersPaneToggle );
			},

			createItemsMovedFeedback: function(){
				/*
				this.views.add( new AttachmentsBrowserItemsMovedFeedback({
					controller: this.controller,
					browser: 	this
				}) );
				*/
	            var notificationCenter = new NotificationCenter( {
	                collection: notifications
	            } );

	            jQuery( 'body' ).append( notificationCenter.render().el );
			},

			createToolbar: function() {
				AttachmentsBrowser.prototype.createToolbar.call( this );

				/*if ( ! this.controller.options.modal ) {
					this.toolbar.set( 'MediaLibraryTaxonomyFilter', new WickedFoldersAttachmentFilter({
						controller: this.controller,
						model:      this.collection.props,
						priority: 	-75
					}).render() );
				}*/
			},

			createSingle: function() {
				var sidebar = this.sidebar,
					single = this.options.selection.single();

				AttachmentsBrowser.prototype.createSingle.call( this );

				sidebar.set( 'wicked-folders', new wickedfolders.views.AttachmentFolders({
					controller: 			this.controller,
					rerenderOnModelChange: 	true,
					model: 					single,
					priority:   			120,
					wickedFolders: 			folders,
					browser: 				this,
					showItemCount: 			this.controller.options.modal ? false : this.folderPaneController.get( 'showItemCount' )
				}) );

			},

			disposeSingle: function() {
				var sidebar = this.sidebar;

				AttachmentsBrowser.prototype.disposeSingle.call( this );

				sidebar.unset( 'wicked-folders' );

			},

			flashItemsMovedFeedback: function(){
				var $feedback = this.$( '.wicked-items-moved-feedback' );

				// TODO: might need to debounce instead...
				$feedback.show().delay( 750 ).fadeOut( 500 );
			},

			filterAttachments: function(){
				var state = this.controller.state(),
					toolbar = this.controller.views.first( '.media-frame-toolbar' ),
					attachments = this.controller.state().get( 'library' ),
					allAttachments = wp.media.model.Attachments.all,
					folder = this.controller.state().get( 'wickedSelectedFolder' ),
					folders = [],
					remove = []
					add = [],
					year = false
					month = false;

				if ( ! folder ) return;

				// Don't filter gallery edit state
				if ( 'gallery-edit' == state.id ) return;

				// Don't filter Document Gallery edit gallery screen
				if ( 'dg-edit' == state.id ) return;

				if ( 'dg-library' == state.id ) {
					// The Document Gallery includes a validator that filters
					// out attachments that have already been added to the
					// gallery; however, this causes a lot of confusion,
					// especially when the folder counts don't match the number
					// of items shown due to the validator filtering out
					// attachments so delete the validator
					delete this.controller.state().get( 'library' ).validator;
				}

				// Only filter term folders (except for 'All Folders' and the unassinged dynamic folder)
				if ( 'Wicked_Folders_Term_Folder' != folder.get( 'type' ) ) {
					if ( '0' != folder.id && 'unassigned_dynamic_folder' != folder.id ) return false;
				};

				// Not entirely clear on how the order filter works when filtering
				// by the native WordPress date filter but it prevents items that
				// have recently been drug to the folder from showing up so I'm
				// deleting it here. Logic has been added below to filter
				// attachments by date so that the date filter still works.
				// See Query.initialize for order filter function.

				// Update 10-12-2021: This is causing problems with the 'load
				// more' functionality added in WordPress 5.8. Unable to
				// reproduce the issue noted above so removing for now.
				//delete attachments.mirroring.filters.order;

				if ( ! _.isUndefined( attachments.mirroring.props ) ) {
					year = attachments.mirroring.props.get( 'year' );
					month = attachments.mirroring.props.get( 'monthnum' );
				}

				allAttachments.each( function( attachment ){
					// Respect the collection's filters (e.g. date, mime, etc.)
					var valid = true;
					if ( attachments.mirroring ) {
						valid = attachments.mirroring.validator( attachment );
					}
					// Since we deleted the order filter earlier, we need to
					// filter by date so the WordPress date filter still works
					if ( year && month ) {
						if ( ! ( year == attachment.get( 'date' ).getFullYear() && month == ( attachment.get( 'date' ).getMonth() + 1 ) ) ) {
							valid = false;
						}
					}
					// Include all items in 'all folders'
					if ( '0' == folder.id ) {
						if ( valid ) {
							add.push( attachment );
						} else {
							remove.push( attachment );
						}
					} else if ( 'unassigned_dynamic_folder' == folder.id ) {
						if ( _.size( attachment.get( 'wickedFolders' ) ) > 0  || ! valid ) {
							remove.push( attachment );
						} else {
							add.push( attachment );
						}
					} else {
						var folders = attachment.get( 'wickedFolders' );

						// If include children is enabled, check if attachment
						// is assigned to any child folder as well
						if ( WickedFoldersProData.includeChildren ) {
							if ( _.isArray( folders ) ) {
								if ( folders.length ) {
									var descendants = allFolders.descendantIds( folder.id ),
										result = _.intersection( folders, descendants );

									if ( result.length ) {
										folders = folders.concat( [ folder.id ] );
									}
								}
							}
						}

						if ( _.isArray( folders ) && folders.length ) {
							if ( -1 == folders.indexOf( folder.id ) ) {
								remove.push( attachment );
							} else if ( valid ) {
								add.push( attachment );
							}
						} else if ( attachment.get( 'uploading' ) ) {
							add.push( attachment );
						} else {
							remove.push( attachment );
						}
					}
				});

				attachments.remove( remove );
				attachments.add( add );

				// The media library only queries 40 attachments initially.  When
				// the page is loaded with a folder already selected and the user
				// navigates to another folder such as 'All Folders', the browser
				// doesn't query for more attachments automatically since it has
				// already been 'set up'.  Call 'more' here to fix that problem

				// Update 3-20-2021: can no longer seem to reproduce above issue
				// but a user has reported the media library continually loading
				// all items which is causing the browser to hang.  Removing
				// this statement for now
				//this.collection.more();

				// Refresh the frame's toolbar to ensure that the insert button
				// is in the correct state
				if ( toolbar ) toolbar.refresh();
			}
		});

		// Extend WordPress Attachments view
		wp.media.view.Attachments = Attachments.extend({
			initialize: function(){
				Attachments.prototype.initialize.apply( this, arguments );

				// TODO: determine if this impacts performance and, if so,
				// find a more efficient approach
				this.collection.on( 'add', this.initDragDrop, this );

			},

			render: function(){

				Attachments.prototype.render.apply( this, arguments );

				this.initDragDrop();

			},

			initDragDrop: function(){
				var view = this,
					collection = this.collection,
					orderby = collection.props.get('orderby'),
					sortingEnabled = 'menuOrder' === orderby || ! collection.comparator,
					folder = this.controller.state().get( 'wickedSelectedFolder' ),
					folderId = ( typeof folder == 'undefined' ) ? false : folder.id;

				// Don't interfere when sorting the attachment selection is enabled
				if ( sortingEnabled ) return;

				this.$( '.attachment' ).draggable( {
					revert: 'invalid',
					cursor: 'default',
					delay: 100,
					zIndex: 200,
					cursorAt: {
						top: -5,
						left: -5
					},
					helper: function( e ){
						if ( view.options.selection.length > 1 ) {
							dragSelection.reset( view.options.selection.models );
							var selection = view.options.selection;
						} else {
							var id = $( e.currentTarget ).attr( 'data-id' ),
								attachment = view.collection.get( id );
								selection = new Backbone.Collection( attachment );
							dragSelection.reset( attachment );
						}
						var dragger = new AttachmentsBrowserDragDetails({
							collection: selection,
							enableCopy: 'unassigned_dynamic_folder' != folderId
						});
						return dragger.render().el;
					},
					start: function(){
						view.$el.addClass( 'wicked-dragging-attachment' );
						view.$el.parent().addClass( 'wicked-dragging-attachment' );
						view.controller.$( '.wicked-tree' ).addClass( 'highlight-assignable' );
					},
					stop: function(){
						view.$el.removeClass( 'wicked-dragging-attachment' );
						view.$el.parent().removeClass( 'wicked-dragging-attachment' );
						view.controller.$( '.wicked-tree' ).removeClass( 'highlight-assignable' );
					}
				} );

				if ( wickedfolders.util.isRtl() ) {
					this.$( '.attachment' ).draggable( 'option', {
						cursorAt: {
							right: -5
						}
					} );
				}
			}
		});

		// Extend WordPress inline uploader
		wp.media.view.UploaderInline = UploaderInline.extend({
			render: function(){

				UploaderInline.prototype.render.apply( this, arguments );

				var folder = this.options.controller.state().get( 'wickedSelectedFolder' ),
					folderId = '0';

				if ( WickedFoldersProData.syncUploadFolderDropdown && ! _.isUndefined( folder ) ) {
					folderId = folder.id;
				}

				var folderSelect = new wickedfolders.views.UploaderFolderSelect({
					el: 						this.$( '#wicked-upload-folder' ),
					controller: 				this.options.controller,
					collection:					folders,
					defaultText:				wickedFoldersL10n.assignToFolder,
					selected:					folderId,
					syncUploadFolderDropdown: 	WickedFoldersProData.syncUploadFolderDropdown,
					hideUnassignable: 			true
				});

				folderSelect.render();

			}
		});

		// TwoColumn is used by media grid view and isn't always loaded
		if ( wp.media.view.Attachment.Details.TwoColumn ) {
			var TwoColumn = wp.media.view.Attachment.Details.TwoColumn;

			wp.media.view.Attachment.Details.TwoColumn = TwoColumn.extend({
				render: function() {
					TwoColumn.prototype.render.apply( this, arguments );

					this.$( '.settings' ).append( '<div class="wicked-folders" />' );

					this.views.add( '.wicked-folders', new wickedfolders.views.AttachmentFolders({
						controller: 			this.controller,
						rerenderOnModelChange: 	true,
						model: 					this.model,
						priority:   			120,
						wickedFolders: 			folders,
						browser: 				this.controller.controller.browserView,
						showItemCount: 			folderPaneController.get( 'showItemCount' )
					}) );
				}
			});
		}

		/*
		var frame = new wp.media.view.MediaFrame.Select({
			// Modal title
			title: 'Select profile background',

			// Enable/disable multiple select
			multiple: true,

			// Library WordPress query arguments.
			library: {
				order: 'ASC',

				// [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo',
				// 'id', 'post__in', 'menuOrder' ]
				orderby: 'title',

				// mime type. e.g. 'image', 'image/jpeg'
				//type: 'image',

				// Searches the attachment title.
				search: null,

				// Attached to a specific post (ID).
				uploadedTo: null
			},

			button: {
				text: 'Set profile background'
			}
		});
		frame.open();
		*/
	});
})(jQuery);
