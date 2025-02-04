{strip}
    <form action="" method="POST">
        <table>
            <tr>
                <td>Sender ID:</td>
                <td><input type="text" name="sender_id" value="{$SENDER_ID}"/></td>
            </tr>
            <tr>
                <td>Server Key:</td>
                <td><input type="text" name="server_key" size="100" value="{$SERVER_KEY}"/></td>
            </tr>
            <tr>
                <td>User ID:</td>
                <td>
                    <input type="text" name="user_id" value="{$USER_ID}" size="10"/>&nbsp;&nbsp;
                    <input type="checkbox" name="is_portal_user" {if $IS_PORTAL_USER}checked{/if}/> Is Portal User
                </td>
            </tr>
            <tr>
                <td>Payload:</td>
                <td><textarea name="payload" rows="10" cols="100">{$PAYLOAD}</textarea></td>
            </tr>
        </table>

        <button>Send</button>
    </form>

    <div id="result"><pre>{$RESULT|print_r}</pre></div>
{/strip}