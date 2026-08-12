import ConsolePage from './ConsolePage';

export default function Health(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="health" />;
}
