import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core';

const httpLink = createHttpLink({
    uri: import.meta.env.VITE_GRAPHQL_ENDPOINT || 'http://localhost:8000/graphql',
});

export const apolloClient = new ApolloClient({
    link: httpLink,
    cache: new InMemoryCache(),
});