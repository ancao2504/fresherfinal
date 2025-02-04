<?php

/*
    MentionUtils
    Author: Hieu Nguyen
    Date: 2021-03-15
    Purpose: to provide util functions to handle mention string
*/

class MentionUtils {

    private static function parseMentions($stringWithMentions) {
        $pattern = '/(?<original>@\[(?<name>([^[]+))]\((?<id>([w+:\d\w-]+))\))/';
        $result = [];

        if (preg_match_all($pattern, $stringWithMentions, $matches)) {
            $result = [
                'matches' => $matches['original'],
                'user_names' => $matches['name'],
                'user_ids' => $matches['id']
            ];
        }

        return $result;
    }

    // Get list of mentioned user in comment string
    public static function getMentionedUsers($stringWithMentions) {
        $mentions = self::parseMentions($stringWithMentions);
        $mentionedUsers = [];

        foreach ($mentions['matches'] as $i => $match) {
            if (!empty($mentions['user_ids'][$i])) {
                $mentionedUsers[$mentions['user_ids'][$i]] = $mentions['user_names'][$i];
            }
        }

        return $mentionedUsers;
    }

    // Render saved comment string into @mention format
    public static function toDisplay($stringWithMentions) {
        $mentions = self::parseMentions($stringWithMentions);
        $stringToDisplay = $stringWithMentions;

        foreach ($mentions['matches'] as $i => $match) {
            if (!empty($mentions['user_ids'][$i])) {
                $replacement = '<mention contenteditable="false" id="'. $mentions['user_ids'][$i] .'">@'. $mentions['user_names'][$i] .'</mention>';
                $stringToDisplay = str_replace($match, $replacement, $stringToDisplay);
            }
        }

        return $stringToDisplay;
    }
}