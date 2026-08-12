import ConsolePage from './ConsolePage';

export default function Commercial(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="overview" />;
}
