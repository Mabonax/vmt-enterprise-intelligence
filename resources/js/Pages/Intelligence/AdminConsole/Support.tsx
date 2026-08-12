import ConsolePage from './ConsolePage';

export default function Support(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="overview" />;
}
