{if is_numeric(strtolower(trim($const)))}
    int
{elseif strtolower(trim($const)) == "true" ||
        strtolower(trim($const)) == "false"}
    bool
{elseif strtolower(trim($const)) == "null"}
    null
{else}
    string
{/if}