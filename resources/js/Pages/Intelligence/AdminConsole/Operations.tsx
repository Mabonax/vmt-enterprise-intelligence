import ConsolePage from './ConsolePage';

export default function Operations(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="overview" />;
}
