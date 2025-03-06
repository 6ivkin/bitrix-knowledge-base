<? 

/** Класс компонента: Карточка обратной связи внизу */

namespace Terentev;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use CIBlockElement;

class TerentevCardFeedback extends \CBitrixComponent implements Controllerable {
    
    // Основной метод компонента
    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function configureActions()
    {
        return [
            'sendFeedback' => [ // Название действия
                'prefilters' => [
                    new HttpMethod([HttpMethod::METHOD_POST]), // Разрешаем только POST-запросы
                    new Csrf(false), // Проверка на CSRF
                ],
            ],
        ];
    }

    // Обработка отправки формы
    public function sendFeedbackAction($name, $message, $url)
    {
        global $USER;
        // Проверка на подключение модуля im
        if (!\CModule::IncludeModule("im")) {
            return AjaxJson::createError(null, ['message' => 'Модуль im не подключен']); 
        }

        // Формируем сообщение для чата
        $chatMessage = "Адрес страницы: " . htmlspecialchars($url) . "\n";
        $chatMessage .= "Тема вопроса: " . htmlspecialchars($name) . "\n";
        $chatMessage .= "Вопрос: " . htmlspecialchars($message);

        // ID пользователя, которому нужно отправить сообщение (например, ID техподдержки)
        $toChatId = 2244;
        $chatId = \CIMMessage::GetChatId($toChatId, $USER->GetID(), true);
        // Отправка сообщения
        $result = \CIMMessenger::Add([
            "TO_CHAT_ID" => $chatId,
            "TO_USER_ID" => $toChatId,
            "MESSAGE" => $chatMessage,
            "MESSAGE_TYPE" => IM_MESSAGE_PRIVATE, // отправляет сообщения лично 
            "FROM_USER_ID" => $USER->GetID(),
            "NOTIFY_TYPE" => IM_NOTIFY_FROM,
            "NOTIFY_MODULE" => "im",
            'PUSH' => 'Y', 
        ]);

        // Проверка результата отправки
        if ($result) {
            return AjaxJson::createSuccess(['message' => 'Сообщение успешно отправлено', 'user'=>$chatId]);
        }

        return AjaxJson::createError(null, ['message' => 'Ошибка при отправке сообщения', 'user'=>$chatId]);
    }

}

?>