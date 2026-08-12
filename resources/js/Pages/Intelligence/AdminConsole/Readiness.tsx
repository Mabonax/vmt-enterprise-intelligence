import ConsolePage from './ConsolePage';

export default function Readiness(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="readiness" />;
}
