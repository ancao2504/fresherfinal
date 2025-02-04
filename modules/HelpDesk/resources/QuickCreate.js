/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.28
 * @desc HelpDesk QuickCreate Js
 */

function loadScript(url, callback) {
    let script = document.createElement('script');
    
    script.type = 'text/javascript';
    script.src = url;
    script.onreadystatechange = callback;
    script.onload = callback;

    document.head.appendChild(script);
}

loadScript('modules/HelpDesk/resources/Form.js', () => {
    class HelpDesk_QuickCreate_Js extends HelpDesk_Form_Js {}
    $(() => { new HelpDesk_QuickCreate_Js() });
});
