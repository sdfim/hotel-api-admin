#!/bin/bash

# Пары томов: источник -> назначение
declare -A VOLUMES=(
  [ujv_certbot_conf]=fora_certbot_conf
  [ujv_certbot_www]=fora_certbot_www
  [ujv_mysql]=fora_mysql
  [ujv_mysql_8]=fora_mysql_8
  [ujv_ollama_data]=fora_ollama_data
  [ujv_pg_data]=fora_pg_data
  [ujv_rabbitmq_data]=fora_rabbitmq_data
  [ujv_redis]=fora_redis
)

# Перенос данных
for src in "${!VOLUMES[@]}"; do
  dst="${VOLUMES[$src]}"
  echo "📦 Копируем $src → $dst"
  
  # Создаем целевой том, если его нет
  docker volume inspect "$dst" >/dev/null 2>&1 || docker volume create "$dst"

  # Копируем через временный контейнер
  docker run --rm \
    -v "$src":/from \
    -v "$dst":/to \
    alpine ash -c "cd /from && cp -a . /to"
done

# Подтверждение удаления
read -p "❗ Удалить все старые ujv_ volumes? [y/N]: " confirm
if [[ "$confirm" =~ ^[Yy]$ ]]; then
  for src in "${!VOLUMES[@]}"; do
    echo "🗑 Удаляем $src"
    docker volume rm "$src"
  done
else
  echo "⏹ Удаление отменено."
fi
