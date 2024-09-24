APP_NAME=booking-engine

echo "------- DATADOG AGENT - START SETTING -------"

sh -c "sed 's/api_key:.*/api_key: $DATADOG_API_KEY/' /etc/datadog-agent/datadog.yaml.example > /etc/datadog-agent/datadog.yaml"

echo "------- DATADOG AGENT - API KEY SETTING DONE -------"

sh -c "sed -i 's/# hostname:.*/hostname: $APP_NAME-$(hostname)/' /etc/datadog-agent/datadog.yaml"

echo "------- DATADOG AGENT - HOSTNAME SETTING DONE -------"

sh -c "sed -i '/^# tags:/s/^# //; /^tags:/a\  - app:$APP_NAME' /etc/datadog-agent/datadog.yaml"

echo "------- DATADOG AGENT - TAGS SETTING DONE -------"

sh -c "sed -i 's/# logs_enabled:.*/logs_enabled: true/' /etc/datadog-agent/datadog.yaml"

echo "------- DATADOG AGENT - ENABLE LOGS SETTING DONE -------"

sh -c "service datadog-agent restart"

echo "------- DATADOG AGENT - FINISHED SETTING -------"

