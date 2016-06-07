<?php
namespace PSB\Core;


class KnownSettingsEnum
{
    const ENDPOINT_NAME = 'PSB.EndpointName';
    const CONTAINER = 'PSB.Container';
    const ENABLED_PERSISTENCES = 'PSB.Persistence.EnabledPersistences';
    const SUPPORTED_STORAGE_TYPE_VALUES = 'PSB.Persistence.SupportedStorageTypeValues';
    const FEATURE_FQCN_LIST = 'PSB.Feature.FQCNList';
    const LOCAL_ADDRESS = 'PSB.Transport.LocalAddress';
    const ERROR_QUEUE = 'PSB.Transport.ErrorQueue';
    const PURGE_ON_STARTUP = 'PSB.Transport.PurgeOnStartup';
    const DURABLE_MESSAGING_ENABLED = 'PSB.Transport.DurableMessagingEnabled';
    const SEND_ONLY = 'PSB.Bus.SendOnly';
    const INSTALLERS_ENABLED = 'PSB.Bus.InstallersEnabled';
    const CREATE_QUEUES = 'PSB.Bus.CreateQueues';
    const DAYS_TO_KEEP_DEDUPLICATION_DATA = 'PSB.Outbox.DaysToKeepDeduplicationData';
    const MAX_FLR_RETRIES = 'PSB.FirstLevelRetry.MaxRetries';
}
