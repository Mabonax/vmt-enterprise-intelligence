import ConsolePage from './ConsolePage';

export default function Alerts(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="alerts" />;
}
