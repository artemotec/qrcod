import qrcode
import hmac
import hashlib
import base64
import urllib.parse

# 1. СЕКРЕТНЫЙ КЛЮЧ (Никому его не показывайте! Он должен быть только в этом скрипте и на вашем сервере)
SECRET_KEY = b"my_super_secret_key_12345"


def generate_signed_url(base_url, payload):
    """
    Генерирует ссылку с криптографической подписью.
    """
    # Создаем подпись данных с помощью надежного алгоритма HMAC-SHA256
    signature = hmac.new(SECRET_KEY, payload.encode("utf-8"), hashlib.sha256).digest()

    # Кодируем подпись в формат, безопасный для URL
    signature_b64 = base64.urlsafe_b64encode(signature).decode("utf-8").rstrip("=")

    # Формируем итоговую ссылку: передаем сами данные и их подпись
    encoded_payload = urllib.parse.quote(payload)
    return f"{base_url}?data={encoded_payload}&sig={signature_b64}"


def create_qr_code(url, filename="secure_qr.png"):
    """
    Генерирует изображение QR-кода.
    """
    qr = qrcode.QRCode(
        version=1,
        error_correction=qrcode.constants.ERROR_CORRECT_H,  # Высокий уровень коррекции ошибок
        box_size=10,
        border=4,
    )
    qr.add_data(url)
    qr.make(fit=True)

    # Можно изменить цвета (fill_color="black", back_color="white")
    img = qr.make_image(fill_color="black", back_color="white")
    img.save(filename)
    print(f"✅ QR-код успешно сохранен как: {filename}")
    print(f"🔗 Содержимое QR-кода: {url}")


# --- ПРИМЕР ИСПОЛЬЗОВАНИЯ ---

# Куда должен вести QR-код
BASE_URL = "https://lipetskcity.ru/404/1/"

# Данные, которые мы хотим защитить от изменения (например, идентификатор баннера)
# Злоумышленник не сможет изменить "banner_center_moscow" на что-то другое.
PAYLOAD_DATA = "source=banner_center_moscow"

# Генерируем безопасную ссылку и сам код
signed_url = generate_signed_url(BASE_URL, PAYLOAD_DATA)
create_qr_code(signed_url, "banner_moscow_qr.png")
