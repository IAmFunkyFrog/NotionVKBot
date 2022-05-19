<?php namespace NotionVK\Bot;

class SessionState
{
    // initial state for new users
    const UNITIALIZED = 0;
    /*
     * state with $_SESSION param included:
     * workspace_meta - encoded json data returned from Notion.so with authentification (?Needed. TODO: Delete redundant)
     * notion_secret - Notion secret returned after authentification
     */
    const INITIALIZED_SECRET = 1;
    /*
     * state with $_SESSION param included:
     * workspace_meta - encoded json data returned from Notion.so with authentification (?Needed)
     * notion_secret - Notion secret returned after authentification
     * chosen_database_id - id of selected database
     */
    const DATABASE_SELECTED = 2;
    /*
     * state with $_SESSION param included:
     * workspace_meta - encoded json data returned from Notion.so with authentification (?Needed)
     * notion_secret - Notion secret returned after authentification
     * chosen_database_id - id of selected database
     */
    const PAGE_ADDING = 3;
    /*
     * state with $_SESSION param included:
     * workspace_meta - encoded json data returned from Notion.so with authentification (?Needed)
     * notion_secret - Notion secret returned after authentification
     * chosen_database_id - id of selected database
     */
    const PAGE_DELETING = 4;

    public static function setUnitialized()
    {
        $_SESSION["state"] = SessionState::UNITIALIZED;
    }

    public static function setInitializedSecret(string $workspace_meta, string $notion_secret)
    {
        $_SESSION["state"] = SessionState::INITIALIZED_SECRET;
        $_SESSION["workspace_meta"] = $workspace_meta;
        $_SESSION["notion_secret"] = $notion_secret;
    }

    public static function setDatabaseSelected(string $workspace_meta, string $notion_secret, string $chosen_database_id)
    {
        $_SESSION["state"] = SessionState::DATABASE_SELECTED;
        $_SESSION["workspace_meta"] = $workspace_meta;
        $_SESSION["notion_secret"] = $notion_secret;
        $_SESSION["chosen_database_id"] = $chosen_database_id;
    }

    public static function setPageAdding(string $workspace_meta, string $notion_secret, string $chosen_database_id)
    {
        $_SESSION["state"] = SessionState::PAGE_ADDING;
        $_SESSION["workspace_meta"] = $workspace_meta;
        $_SESSION["notion_secret"] = $notion_secret;
        $_SESSION["chosen_database_id"] = $chosen_database_id;
    }

    public static function setPageDeleting(string $workspace_meta, string $notion_secret, string $chosen_database_id)
    {
        $_SESSION["state"] = SessionState::PAGE_DELETING;
        $_SESSION["workspace_meta"] = $workspace_meta;
        $_SESSION["notion_secret"] = $notion_secret;
        $_SESSION["chosen_database_id"] = $chosen_database_id;
    }
}
