<?php
/**
 * Name: MP3 File Handler
 * Author: Shish <webmaster@shishnet.org>
 * Description: Handle MP3 files
 */

class MP3FileHandler extends Extension {
	var $theme;

	public function receive_event($event) {
		if(is_null($this->theme)) $this->theme = get_theme_object("handle_mp3", "MP3FileHandlerTheme");

		if(is_a($event, 'DataUploadEvent') && $event->type == "mp3" && $this->check_contents($event->tmpname)) {
			$hash = $event->hash;
			$ha = substr($hash, 0, 2);
			if(!copy($event->tmpname, "images/$ha/$hash")) {
				$event->veto("MP3 Handler failed to move file from uploads to archive");
				return;
			}
			send_event(new ThumbnailGenerationEvent($event->hash, $event->type));
			$image = $this->create_image_from_data("images/$ha/$hash", $event->metadata);
			if(is_null($image)) {
				$event->veto("MP3 Handler failed to create image object from data");
				return;
			}
			send_event(new ImageAdditionEvent($event->user, $image));
		}

		if(is_a($event, 'ThumbnailGenerationEvent') && $event->type == "mp3") {
			$hash = $event->hash;
			$ha = substr($hash, 0, 2);
			// FIXME: scale image, as not all boards use 192x192
			copy("ext/handle_mp3/thumb.jpg", "thumbs/$ha/$hash");
		}

		if(is_a($event, 'DisplayingImageEvent') && $event->image->ext == "mp3") {
			$this->theme->display_image($event->page, $event->image);
		}
	}

	private function create_image_from_data($filename, $metadata) {
		global $config;

		$image = new Image();

		// FIXME: need more flash format specs :|
		$image->width = 0;
		$image->height = 0;
		
		$image->filesize  = $metadata['size'];
		$image->hash      = $metadata['hash'];
		$image->filename  = $metadata['filename'];
		$image->ext       = $metadata['extension'];
		$image->tag_array = tag_explode($metadata['tags']);
		$image->source    = $metadata['source'];

		return $image;
	}

	private function check_contents($file) {
		// FIXME: mp3 magic header?
		return (file_exists($file));
	}
}
add_event_listener(new MP3FileHandler());
?>
