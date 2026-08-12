import ConsolePage from './ConsolePage';

export default function Technical(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="overview" />;
}
