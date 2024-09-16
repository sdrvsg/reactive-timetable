<?php

namespace App\Telegram\Mixins;

use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Input\InputMediaPhoto;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class ImagedMessage
{
    public function sendImagedMessage(): \Closure
    {
        return function (string $text, InlineKeyboardMarkup|ReplyKeyboardMarkup|null $buttons = null, array $options = []) {

            $options['reply_markup'] = $buttons;
            $options['parse_mode'] = ParseMode::HTML;
            $options['disable_web_page_preview'] = true;

            if (array_key_exists('image', $options) && $options['image']) {

                // $options['show_caption_above_media'] = true;
                $image = $options['image'];

                unset($options['image']);
                unset($options['link_preview_options']);

                return $this->sendPhoto(
                    mb_strpos($image, 'https') !== false ? $image : InputFile::make(fopen($image, 'r+')),
                    ... $options,
                    caption: $text
                );

            } else {

                unset($options['image']);
                return $this->sendMessage($text, ... $options);

            }

        };
    }

    public function editImagedMessage(): \Closure
    {
        return function (string $text, InlineKeyboardMarkup|ReplyKeyboardMarkup|null $buttons = null, array $options = []) {

            $options['reply_markup'] = $buttons;
            $options['parse_mode'] = ParseMode::HTML;
            $options['disable_web_page_preview'] = true;
            unset($options['reply_to_message_id']);

            try {

                if (array_key_exists('image', $options) && $options['image']) {

                    // $options['show_caption_above_media'] = true;
                    $image = $options['image'];

                    unset($options['image']);
                    unset($options['link_preview_options']);
                    unset($options['message_effect_id']);
                    unset($options['parse_mode']);

                    return $this->editMessageMedia(
                        ... $options,
                        media: InputMediaPhoto::make(
                            media: mb_strpos($image, 'https') !== false ? $image : InputFile::make(fopen($image, 'r+')),
                            caption: $text,
                            parse_mode: ParseMode::HTML,
                        ),
                    );

                } else {

                    unset($options['image']);
                    return $this->editMessageText(
                        ... $options,
                        text: $text,
                    );

                }

            } catch (TelegramException $e) {
                if (!str_contains($e->getMessage(), 'message is not modified'))
                    throw new TelegramException($e->getMessage());
            }

            return null;
        };
    }
}
