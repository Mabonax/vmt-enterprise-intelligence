import ConsolePage from './ConsolePage';

export default function Actions(props: Parameters<typeof ConsolePage>[0]) {
    return <ConsolePage {...props} focus="actions" />;
}
