import {gql} from "graphql-tag";

export const GET_LOGS = gql`
    query GetLogs($perPage: Int, $page: Int) {
        allLogs(perPage: $perPage, page: $page) {
            items {
                  action
                  target
                  payload
                  status
                  created_at
            }
            currentPage
            totalPages
            total
            hasNextPage
            hasPreviousPage
        }
    }
`;